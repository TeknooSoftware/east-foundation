<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Liveness;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Liveness\PingService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Liveness\PingService
 */
class PingServiceTest extends TestCase
{
    public function testRegister()
    {
        self::assertInstanceOf(
            PingService::class,
            (new PingService())->register('foo', function () {}),
        );
    }

    public function testUnregisterNotRegistered()
    {
        self::assertInstanceOf(
            PingService::class,
            (new PingService())->unregister('foo'),
        );
    }

    public function testUnregister()
    {
        self::assertInstanceOf(
            PingService::class,
            (new PingService())
                ->register('foo', function () {})
                ->unregister('foo'),
        );
    }

    public function testPing()
    {
        $call1 = 0;
        $call2 = 0;
        $f1 = function () use (&$call1): void {
            $call1++;
        };
        $f2 = function () use (&$call2): void {
            $call2++;
        };

        $service = new PingService();
        $service->register('f1', $f1);
        $service->register('f2', $f2);

        self::assertInstanceOf(
            PingService::class,
            $service->ping()
        );
        self::assertInstanceOf(
            PingService::class,
            $service->ping()
        );

        $service->unregister('f1');
        self::assertInstanceOf(
            PingService::class,
            $service->ping()
        );

        self::assertEquals(2, $call1);
        self::assertEquals(3, $call2);
    }
}