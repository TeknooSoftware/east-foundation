<?php

/*
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

namespace Teknoo\East\Diactoros;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\Exception\CallbackStreamException;
use Teknoo\East\Foundation\Http\Message\CallbackStreamFactoryInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Http\Message\ChunkGeneratorInterface;
use Teknoo\East\Foundation\Http\Message\SseGeneratorInterface;

use function connection_aborted;
use function explode;
use function fclose;
use function flush;
use function fopen;
use function ignore_user_abort;
use function is_string;
use function ob_flush;
use function ob_get_level;
use function str_replace;
use function stream_get_contents;

/**
 * Adapter of Laminas\Diactoros\CallbackStream for CallbackStreamInterface
 *
 * The callback of a stream of a generator reads the generator with a `foreach`, writes each chunk with `echo` and sends
 * it with `flush()`. It stops the generator when the HTTP client closes the connection. During the loop,
 * `ignore_user_abort()` is on, so the generator stops also in the classic mode of PHP.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CallbackStreamFactory implements StreamFactoryInterface, CallbackStreamFactoryInterface
{
    /**
     * @var callable
     */
    private $connectionAbortedCallable;

    public function __construct()
    {
        $this->connectionAbortedCallable = connection_aborted(...);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return new CallbackStream(
            static fn (): string => $content
        );
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new CallbackStream(
            static function () use ($filename, $mode): string {
                $hF = @fopen($filename, $mode);
                if (!$hF) {
                    throw new CallbackStreamException("Can not open $filename");
                }

                $content = (string) stream_get_contents($hF);
                fclose($hF);

                return $content;
            }
        );
    }

    /**
     * @param resource $resource
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new CallbackStream(
            static fn (): string => (string) stream_get_contents($resource)
        );
    }

    public function createStreamFromGenerator(ChunkGeneratorInterface $generator): CallbackStreamInterface
    {
        $connectionAborted = $this->connectionAbortedCallable;

        $streamType = CallbackStreamInterface::CHUNK_STREAM_TYPE;
        if ($generator instanceof SseGeneratorInterface) {
            $streamType = CallbackStreamInterface::SSE_STREAM_TYPE;
        }

        return new CallbackStream(
            static function () use ($generator, $streamType, $connectionAborted): string {
                //In the classic mode of PHP, the script must not stop at the close of the connection
                $previousIgnoreUserAbort = ignore_user_abort(true);

                try {
                    foreach ($generator as $key => $chunk) {
                        if (CallbackStreamInterface::SSE_STREAM_TYPE === $streamType) {
                            $field = 'data';
                            $event = '';
                            if (SseGeneratorInterface::COMMENT === $key) {
                                $field = '';
                            } elseif (is_string($key)) {
                                $event = 'event: ' . $key . "\n";
                            }

                            $lines = '';
                            foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $chunk)) as $line) {
                                $lines .= $field . ': ' . $line . "\n";
                            }

                            $chunk = $event . $lines . "\n";
                        }

                        echo $chunk;
                        if (0 < ob_get_level()) {
                            ob_flush();
                        }

                        flush();

                        if (1 === $connectionAborted()) {
                            break;
                        }
                    }
                } finally {
                    ignore_user_abort((bool) $previousIgnoreUserAbort);
                }

                return '';
            },
            $streamType,
        );
    }
}
