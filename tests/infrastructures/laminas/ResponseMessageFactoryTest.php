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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Diactoros;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Diactoros\ResponseMessageFactory;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Diactoros\ResponseMessageFactory
 */
class ResponseMessageFactoryTest extends TestCase
{
    public function buildFactory(): ResponseMessageFactory
    {
        return new ResponseMessageFactory();
    }

    public function testCreateMessage()
    {
        self::assertInstanceOf(
            MessageInterface::class,
            $message = $this->buildFactory()->createMessage('1.1')
        );
        
        self::assertInstanceOf(
            ResponseInterface::class,
            $message
        );

        self::assertEquals(
            '1.1',
            $message->getProtocolVersion()
        );
    }
}
