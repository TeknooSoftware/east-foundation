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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Liveness\Exception\TimeLimitReachedException;
use Teknoo\East\Foundation\Liveness\TimeoutService;
use Teknoo\East\Foundation\Time\TimerService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimeoutService::class)]
class TimeoutServiceTest extends TestCase
{
    private ?TimerService $timer = null;

    private ?int $seconds = null;

    public function getTimerMock(): TimerService&MockObject
    {
        if (null === $this->timer) {
            $this->timer = $this->createMock(TimerService::class);
        }

        return $this->timer;
    }

    public function createService(bool $withTimer = true): TimeoutService
    {
        if ($withTimer) {
            $service = new TimeoutService($this->getTimerMock());
        } else {
            $service = new TimeoutService(null);
        }

        $rp = new \ReflectionProperty(TimeoutService::class, 'setTimeoutCallable');
        $rp->setValue($service, function (int $seconds): void {
            $this->seconds = $seconds;
        });

        return $service;
    }

    public function testThrowException(): void
    {
        $this->expectException(TimeLimitReachedException::class);
        TimeoutService::throwException();
    }

    public function testEnable(): void
    {
        $service = $this->createService();
        $this->seconds = null;

        $this->getTimerMock()
            ->expects($this->once())
            ->method('register')
            ->willReturnCallback(
                function (int $seconds, string $timerId) {
                    $this->assertEquals(10, $seconds);
                    $this->assertEquals(TimeoutService::class, $timerId);
                    return $this->getTimerMock();
                }
            );

        $this->assertInstanceOf(
            TimeoutService::class,
            $service->enable(10),
        );

        $this->assertEquals(15, $this->seconds);
    }

    public function testEnableWithoutTimer(): void
    {
        $service = $this->createService(false);
        $this->seconds = null;

        $this->assertInstanceOf(
            TimeoutService::class,
            $service->enable(10),
        );

        $this->assertEquals(15, $this->seconds);
    }

    public function testDisable(): void
    {
        $service = $this->createService();

        $this->getTimerMock()
            ->expects($this->atLeastOnce())
            ->method('unregister')
            ->willReturnCallback(
                function (string $timerId) {
                    $this->assertEquals(TimeoutService::class, $timerId);
                    return $this->getTimerMock();
                }
            );

        $this->assertInstanceOf(
            TimeoutService::class,
            $service->disable(),
        );
    }

    public function testDisableWithoutTimer(): void
    {
        $service = $this->createService(false);

        $this->assertInstanceOf(
            TimeoutService::class,
            $service->disable(),
        );
    }
}
