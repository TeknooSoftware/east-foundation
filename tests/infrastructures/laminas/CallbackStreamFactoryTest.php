<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Diactoros;

use Generator;
use ReflectionProperty;
use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Http\Message\ChunkGeneratorInterface;
use Teknoo\East\Foundation\Http\Message\SseGeneratorInterface;

use function ignore_user_abort;
use function ob_end_clean;
use function ob_start;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CallbackStreamFactory::class)]
class CallbackStreamFactoryTest extends TestCase
{
    public function buildFactory(): CallbackStreamFactory
    {
        return new CallbackStreamFactory();
    }

    public function testCreateStream(): void
    {
        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStream('foo')
        );

        $this->assertEquals(
            'foo',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromFile(): void
    {
        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromFile('php://memory', 'r')
        );

        $this->assertEquals(
            '',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromFileNotReadable(): void
    {
        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromFile('/aaaaa', 'r')
        );

        $this->expectException(RuntimeException::class);
        $stream->getContents();
    }

    public function testcreateStreamFromResource(): void
    {
        $hf = fopen('php://memory', 'rw+');
        fwrite($hf, 'fooBarContent');
        \fseek($hf, 0);

        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromResource($hf)
        );

        $this->assertEquals(
            'fooBarContent',
            $stream->getContents($hf)
        );
    }

    /**
     * @return list<string>
     */
    private function flushedChunks(CallbackStreamInterface $stream): array
    {
        $flushed = [];
        ob_start(
            static function (string $buffer) use (&$flushed): string {
                if ('' !== $buffer) {
                    $flushed[] = $buffer;
                }

                return '';
            }
        );

        $content = $stream->getContents();
        ob_end_clean();

        $this->assertSame('', $content);

        return $flushed;
    }

    public function testCreateStreamFromGenerator(): void
    {
        $generator = new class () implements ChunkGeneratorInterface {
            public function getIterator(): Generator
            {
                yield 'foo';
                yield 'bar';
                yield 'baz';
            }
        };

        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromGenerator($generator)
        );

        $this->assertSame(
            CallbackStreamInterface::CHUNK_STREAM_TYPE,
            $stream->getMetadata('stream_type')
        );

        $this->assertSame(
            ['foo', 'bar', 'baz'],
            $this->flushedChunks($stream)
        );
    }

    public function testCreateStreamFromSseGenerator(): void
    {
        $generator = new class () implements SseGeneratorInterface {
            public function getIterator(): Generator
            {
                yield 'message_start' => '{"type":"message_start"}';
                yield SseGeneratorInterface::COMMENT => 'keep-alive';
                yield 'foo';
                yield "foo\nbar\r\nbaz\rqux";
                yield 'ping' => "a\nb";
            }
        };

        $this->assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromGenerator($generator)
        );

        $this->assertSame(
            CallbackStreamInterface::SSE_STREAM_TYPE,
            $stream->getMetadata('stream_type')
        );

        $this->assertSame(
            [
                "event: message_start\ndata: {\"type\":\"message_start\"}\n\n",
                ": keep-alive\n\n",
                "data: foo\n\n",
                "data: foo\ndata: bar\ndata: baz\ndata: qux\n\n",
                "event: ping\ndata: a\ndata: b\n\n",
            ],
            $this->flushedChunks($stream)
        );
    }

    public function testCreateStreamFromGeneratorStopsAfterAClosedConnection(): void
    {
        $generator = new class () implements ChunkGeneratorInterface {
            /**
             * @var list<string>
             */
            public array $produced = [];

            public bool $stopped = false;

            public function getIterator(): Generator
            {
                try {
                    foreach (['foo', 'bar', 'baz'] as $chunk) {
                        $this->produced[] = $chunk;

                        yield $chunk;
                    }
                } finally {
                    $this->stopped = true;
                }
            }
        };

        $factory = $this->buildFactory();
        $calls = 0;
        $property = new ReflectionProperty(CallbackStreamFactory::class, 'connectionAbortedCallable');
        $property->setValue(
            $factory,
            static function () use (&$calls): int {
                ++$calls;

                if (2 <= $calls) {
                    return 1;
                }

                return 0;
            }
        );

        $stream = $factory->createStreamFromGenerator($generator);

        $this->assertSame(
            ['foo', 'bar'],
            $this->flushedChunks($stream)
        );
        $this->assertSame(['foo', 'bar'], $generator->produced);
        $this->assertTrue($generator->stopped);
        $this->assertSame(2, $calls);
    }

    public function testCreateStreamFromGeneratorIgnoresUserAbortDuringTheLoop(): void
    {
        $generator = new class () implements ChunkGeneratorInterface {
            /**
             * @var list<int>
             */
            public array $ignoreUserAbort = [];

            public function getIterator(): Generator
            {
                $this->ignoreUserAbort[] = ignore_user_abort();

                yield 'foo';
            }
        };

        $previous = ignore_user_abort(false);

        $stream = $this->buildFactory()->createStreamFromGenerator($generator);
        $this->assertSame(['foo'], $this->flushedChunks($stream));

        $this->assertSame([1], $generator->ignoreUserAbort);
        $this->assertSame(0, ignore_user_abort((bool) $previous));
    }

    public function testCreateStreamFromGeneratorRestoresUserAbortAfterAnError(): void
    {
        $generator = new class () implements ChunkGeneratorInterface {
            public function getIterator(): Generator
            {
                yield 'foo';

                throw new RuntimeException('Generator error');
            }
        };

        $previous = ignore_user_abort(false);

        $stream = $this->buildFactory()->createStreamFromGenerator($generator);

        $error = null;
        ob_start(static fn (string $buffer): string => '');
        try {
            $stream->getContents();
        } catch (RuntimeException $exception) {
            $error = $exception;
        } finally {
            ob_end_clean();
        }

        $this->assertInstanceOf(RuntimeException::class, $error);
        $this->assertSame('Generator error', $error->getMessage());
        $this->assertSame(0, ignore_user_abort((bool) $previous));
    }

    public function testCreateStreamFromSseGeneratorStopsAfterAClosedConnection(): void
    {
        $generator = new class () implements SseGeneratorInterface {
            /**
             * @var list<string>
             */
            public array $produced = [];

            public bool $stopped = false;

            public function getIterator(): Generator
            {
                try {
                    foreach (['foo', 'bar', 'baz'] as $data) {
                        $this->produced[] = $data;

                        yield $data;
                    }
                } finally {
                    $this->stopped = true;
                }
            }
        };

        $factory = $this->buildFactory();
        $calls = 0;
        $property = new ReflectionProperty(CallbackStreamFactory::class, 'connectionAbortedCallable');
        $property->setValue(
            $factory,
            static function () use (&$calls): int {
                ++$calls;

                if (2 <= $calls) {
                    return 1;
                }

                return 0;
            }
        );

        $stream = $factory->createStreamFromGenerator($generator);

        $this->assertSame(
            ["data: foo\n\n", "data: bar\n\n"],
            $this->flushedChunks($stream)
        );
        $this->assertSame(['foo', 'bar'], $generator->produced);
        $this->assertTrue($generator->stopped);
        $this->assertSame(2, $calls);
    }

    public function testCreateStreamFromSseGeneratorIgnoresUserAbortDuringTheLoop(): void
    {
        $generator = new class () implements SseGeneratorInterface {
            /**
             * @var list<int>
             */
            public array $ignoreUserAbort = [];

            public function getIterator(): Generator
            {
                $this->ignoreUserAbort[] = ignore_user_abort();

                yield 'foo';
            }
        };

        $previous = ignore_user_abort(false);

        $stream = $this->buildFactory()->createStreamFromGenerator($generator);
        $this->assertSame(["data: foo\n\n"], $this->flushedChunks($stream));

        $this->assertSame([1], $generator->ignoreUserAbort);
        $this->assertSame(0, ignore_user_abort((bool) $previous));
    }

    public function testCreateStreamFromSseGeneratorRestoresUserAbortAfterAnError(): void
    {
        $generator = new class () implements SseGeneratorInterface {
            public function getIterator(): Generator
            {
                yield 'foo';

                throw new RuntimeException('Generator error');
            }
        };

        $previous = ignore_user_abort(false);

        $stream = $this->buildFactory()->createStreamFromGenerator($generator);

        $error = null;
        ob_start(static fn (string $buffer): string => '');
        try {
            $stream->getContents();
        } catch (RuntimeException $exception) {
            $error = $exception;
        } finally {
            ob_end_clean();
        }

        $this->assertInstanceOf(RuntimeException::class, $error);
        $this->assertSame('Generator error', $error->getMessage());
        $this->assertSame(0, ignore_user_abort((bool) $previous));
    }
}
