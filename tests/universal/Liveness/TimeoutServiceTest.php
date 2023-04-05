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

namespace Teknoo\Tests\East\Foundation\Liveness;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Liveness\Exception\TimeLimitReachedException;
use Teknoo\East\Foundation\Liveness\TimeoutService;
use Teknoo\East\Foundation\Time\TimerService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Foundation\Liveness\TimeoutService
 */
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
        $rp->setAccessible(true);
        $rp->setValue($service, function (int $seconds) {
            $this->seconds = $seconds;
        });

        return $service;
    }

    public function testThrowException()
    {
        $this->expectException(TimeLimitReachedException::class);
        TimeoutService::throwException();
    }

    public function testEnable()
    {
        $service = $this->createService();
        $this->seconds = null;

        $this->getTimerMock()
            ->expects(self::once())
            ->method('register')
            ->willReturnCallback(
                function (int $seconds, string $timerId) {
                    self::assertEquals(10, $seconds);
                    self::assertEquals(TimeoutService::class, $timerId);
                    return $this->getTimerMock();
                }
            );

        self::assertInstanceOf(
            TimeoutService::class,
            $service->enable(10),
        );

        self::assertEquals(15, $this->seconds);
    }

    public function testEnableWithoutTimer()
    {
        $service = $this->createService(false);
        $this->seconds = null;

        self::assertInstanceOf(
            TimeoutService::class,
            $service->enable(10),
        );

        self::assertEquals(15, $this->seconds);
    }

    public function testDisable()
    {
        $service = $this->createService();

        $this->getTimerMock()
            ->expects(self::atLeastOnce())
            ->method('unregister')
            ->willReturnCallback(
                function (string $timerId) {
                    self::assertEquals(TimeoutService::class, $timerId);
                    return $this->getTimerMock();
                }
            );

        self::assertInstanceOf(
            TimeoutService::class,
            $service->disable(),
        );
    }

    public function testDisableWithoutTimer()
    {
        $service = $this->createService(false);

        self::assertInstanceOf(
            TimeoutService::class,
            $service->disable(),
        );
    }
}