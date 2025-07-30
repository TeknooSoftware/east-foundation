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

namespace Teknoo\Tests\East\Foundation\Liveness;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Liveness\PingService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PingService::class)]
class PingServiceTest extends TestCase
{
    public function testRegister(): void
    {
        $this->assertInstanceOf(
            PingService::class,
            new PingService()->register('foo', function (): void {}),
        );
    }

    public function testUnregisterNotRegistered(): void
    {
        $this->assertInstanceOf(
            PingService::class,
            new PingService()->unregister('foo'),
        );
    }

    public function testUnregister(): void
    {
        $this->assertInstanceOf(
            PingService::class,
            new PingService()
                ->register('foo', function (): void {})
                ->unregister('foo'),
        );
    }

    public function testPing(): void
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

        $this->assertInstanceOf(
            PingService::class,
            $service->ping()
        );
        $this->assertInstanceOf(
            PingService::class,
            $service->ping()
        );

        $service->unregister('f1');
        $this->assertInstanceOf(
            PingService::class,
            $service->ping()
        );

        $this->assertEquals(2, $call1);
        $this->assertEquals(3, $call2);
    }
}
