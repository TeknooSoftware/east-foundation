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

use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Diactoros\CallbackStreamFactory;

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
}
