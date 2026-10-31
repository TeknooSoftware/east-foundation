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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CallbackStream::class)]
class CallbackStreamTest extends TestCase
{
    public function testBind(): void
    {
        $this->assertInstanceOf(
            CallbackStream::class,
            (new CallbackStream(function () {}))->bind(function () {})
        );
    }

    public function testUnbind(): void
    {
        $this->assertInstanceOf(
            CallbackStream::class,
            new CallbackStream(function (): void {})->unbind()
        );
    }

    public function testGetMetadataOfACallbackStream(): void
    {
        $stream = new CallbackStream(static fn (): string => 'foo');

        $this->assertSame(
            ['eof' => false, 'stream_type' => 'callback', 'seekable' => false],
            $stream->getMetadata()
        );
        $this->assertSame('callback', $stream->getMetadata('stream_type'));
        $this->assertFalse($stream->getMetadata('eof'));
        $this->assertNull($stream->getMetadata('foo'));
    }

    public function testGetMetadataWithAStreamType(): void
    {
        $stream = new CallbackStream(static fn (): string => 'foo', CallbackStreamInterface::SSE_STREAM_TYPE);

        $this->assertSame(
            ['eof' => false, 'stream_type' => 'sse', 'seekable' => false],
            $stream->getMetadata()
        );
        $this->assertSame('sse', $stream->getMetadata('stream_type'));
        $this->assertFalse($stream->getMetadata('seekable'));
    }
}
