<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Message::class)]
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
