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

namespace Teknoo\Tests\East\Foundation\Time;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\Backend\Cooperative\TimerService as CooperativeTimerService;
use Teknoo\East\Foundation\Time\Backend\Pcntl\TimerService as PcntlTimerService;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\SleepService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\East\Foundation\Time\TimerServiceInterface;

use function sleep;
use function time;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SleepService::class)]
class SleepServiceTest extends TestCase
{
    private ?TimerServiceInterface $timerService = null;

    public function getTimerServiceMock(): TimerServiceInterface&Stub
    {
        if (!$this->timerService instanceof TimerServiceInterface) {
            $this->timerService = $this->createStub(TimerServiceInterface::class);
        }

        return $this->timerService;
    }

    public function getTimerServiceMockObject(): TimerServiceInterface&MockObject
    {
        if (!$this->timerService instanceof TimerServiceInterface) {
            $this->timerService = $this->createMock(TimerServiceInterface::class);
        }

        return $this->timerService;
    }

    public function testWaitWithMock(): void
    {
        $this->getTimerServiceMockObject()
            ->expects($this->once())
            ->method('register')
            ->willReturnCallback(
                function (int $seconds, string $timerId, callable $callback): TimerServiceInterface&MockObject {
                    sleep($seconds);
                    $callback();

                    return $this->getTimerServiceMockObject();
                }
            );

        $t = time();
        $this->assertInstanceOf(
            SleepService::class,
            new SleepService($this->getTimerServiceMockObject())->wait(2),
        );
        $this->assertSame(
            $t + 2,
            time(),
        );
    }

    public function testWaitWithTimer(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $t = time();
        $this->assertInstanceOf(
            SleepService::class,
            new SleepService(new TimerService(new PcntlTimerService(new DatesService())))->wait(2),
        );
        $this->assertSame(
            $t + 2,
            time(),
        );
    }

    public function testWait0SecondsWithTimer(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $t = time();
        $this->assertInstanceOf(
            SleepService::class,
            new SleepService(new TimerService(new PcntlTimerService(new DatesService())))->wait(0),
        );
        $this->assertLessThanOrEqual(
            $t + 1,
            time(),
        );
    }

    public function testWaitWithCooperativeTimer(): void
    {
        $t = time();
        $this->assertInstanceOf(
            SleepService::class,
            new SleepService(new TimerService(new CooperativeTimerService(new DatesService())))->wait(2),
        );
        $this->assertSame(
            $t + 2,
            time(),
        );
    }

    public function testWait0SecondsWithCooperativeTimer(): void
    {
        $t = time();
        $this->assertInstanceOf(
            SleepService::class,
            new SleepService(new TimerService(new CooperativeTimerService(new DatesService())))->wait(0),
        );
        $this->assertLessThanOrEqual(
            $t + 1,
            time(),
        );
    }

    public function testWaitWithCooperativeTimerExecutesOtherCalls(): void
    {
        $timer = new TimerService(new CooperativeTimerService(new DatesService()));

        $counter = 0;
        $callback = null;
        $callback = function () use ($timer, &$counter, &$callback): void {
            ++$counter;
            $timer->register(
                seconds: 1,
                timerId: 'logging',
                callback: $callback,
            );
        };

        $timer->register(
            seconds: 1,
            timerId: 'logging',
            callback: $callback,
        );

        new SleepService($timer)->wait(3);
        $timer->unregister('logging');

        $this->assertGreaterThanOrEqual(2, $counter);
        $this->assertLessThanOrEqual(3, $counter);
    }
}
