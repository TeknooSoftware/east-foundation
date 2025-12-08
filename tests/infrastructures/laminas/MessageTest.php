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
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\Message;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    public function testProtocolVersion(): void
    {
        $message = new Message();
        $newMessage = $message->withProtocolVersion('1.1');

        $this->assertNotSame(
            $message,
            $newMessage
        );

        $this->assertEquals(
            '1.1',
            $newMessage->getProtocolVersion()
        );
    }

    public function testHeaders(): void
    {
        $message = new Message();

        $this->assertFalse(
            $message->hasHeader('foo')
        );

        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotSame(
            $message,
            $newMessage
        );

        $this->assertFalse(
            $message->hasHeader('foo')
        );

        $this->assertTrue(
            $newMessage->hasHeader('foo')
        );

        $this->assertEquals(
            ['foo' => ['bar']],
            $newMessage->getHeaders()
        );

        $this->assertEquals(
            ['bar'],
            $newMessage->getHeader('foo')
        );

        $newMessage = $newMessage->withAddedHeader('foo', 'bar2');

        $this->assertEquals(
            ['bar', 'bar2'],
            $newMessage->getHeader('foo')
        );
    }

    public function testBody(): void
    {
        $message = new Message();
        $newMessage = $message->withBody($this->createStub(StreamInterface::class));

        $this->assertNotSame(
            $message,
            $newMessage
        );

        $this->assertInstanceOf(
            StreamInterface::class,
            $newMessage->getBody()
        );
    }
}
