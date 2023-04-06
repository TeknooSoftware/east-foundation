<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Diactoros;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\Message;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Diactoros\Message
 */
class MessageTest extends TestCase
{
    public function testProtocolVersion()
    {
        $message = new Message();
        $newMessage = $message->withProtocolVersion('1.1');

        self::assertNotSame(
            $message,
            $newMessage
        );

        self::assertEquals(
            '1.1',
            $newMessage->getProtocolVersion()
        );
    }

    public function testHeaders()
    {
        $message = new Message();

        self::assertFalse(
            $message->hasHeader('foo')
        );

        $newMessage = $message->withHeader('foo', 'bar');

        self::assertNotSame(
            $message,
            $newMessage
        );

        self::assertFalse(
            $message->hasHeader('foo')
        );

        self::assertTrue(
            $newMessage->hasHeader('foo')
        );

        self::assertEquals(
            ['foo' => ['bar']],
            $newMessage->getHeaders()
        );

        self::assertEquals(
            ['bar'],
            $newMessage->getHeader('foo')
        );

        $newMessage = $newMessage->withAddedHeader('foo', 'bar2');

        self::assertEquals(
            ['bar', 'bar2'],
            $newMessage->getHeader('foo')
        );
    }

    public function testBody()
    {
        $message = new Message();
        $newMessage = $message->withBody($this->createMock(StreamInterface::class));

        self::assertNotSame(
            $message,
            $newMessage
        );

        self::assertInstanceOf(
            StreamInterface::class,
            $newMessage->getBody()
        );
    }
}
