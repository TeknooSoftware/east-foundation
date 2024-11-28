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

namespace Teknoo\Tests\East\Foundation\Time;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\SleepService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\East\Foundation\Time\TimerServiceInterface;
use function pcntl_alarm;
use function sleep;
use function str_repeat;
use function time;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SleepService::class)]
class SleepServiceTest extends TestCase
{
    private ?TimerServiceInterface $timerService = null;

    public function getTimerServiceMock(): TimerServiceInterface&MockObject
    {
        if (null === $this->timerService) {
            $this->timerService = $this->createMock(TimerServiceInterface::class);
        }

        return $this->timerService;
    }

    public function testWaitWithMock()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $this->getTimerServiceMock()
            ->expects($this->once())
            ->method('register')
            ->willReturnCallback(
                function (int $seconds, string $timerId, callable $callback) {
                    sleep($seconds);
                    $callback();

                    return $this->getTimerServiceMock();
                }
            );

        $t = time();
        self::assertInstanceOf(
            SleepService::class,
            (new SleepService($this->getTimerServiceMock()))->wait(2),
        );
        self::assertEquals(
            $t + 2,
            time(),
        );
    }

    public function testWaitWithTimer()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $t = time();
        self::assertInstanceOf(
            SleepService::class,
            (new SleepService(new TimerService(new DatesService())))->wait(2),
        );
        self::assertEquals(
            $t + 2,
            time(),
        );
    }

    public function testWait0SecondsWithTimer()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $t = time();
        self::assertInstanceOf(
            SleepService::class,
            (new SleepService(new TimerService(new DatesService())))->wait(0),
        );
        self::assertLessThanOrEqual(
            $t + 1,
            time(),
        );
    }
}
