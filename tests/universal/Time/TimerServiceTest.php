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

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\TimerService;

use function pcntl_alarm;
use function sleep;
use function str_repeat;
use function time;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimerService::class)]
class TimerServiceTest extends TestCase
{
    private ?DatesService $datesService = null;

    public function getDatesServiceMock(): DatesService&MockObject
    {
        if (!$this->datesService instanceof \Teknoo\East\Foundation\Time\DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    public function getDatesServiceStub(): DatesService&Stub
    {
        if (!$this->datesService instanceof \Teknoo\East\Foundation\Time\DatesService) {
            $this->datesService = $this->createStub(DatesService::class);
        }

        return $this->datesService;
    }

    public function testIsAvailable(): void
    {
        $this->assertTrue(TimerService::isAvailable());
    }

    public function testUnregister(): void
    {
        $this->assertInstanceOf(
            TimerService::class,
            new TimerService($this->getDatesServiceStub())->unregister('foo'),
        );
    }

    public function testSimpleRegisterOneFunction(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called): void {
                    $called = true;
                },
            )
        );

        $this->assertFalse($called);
        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called);
    }

    public function testSimpleRegisterOneFunctionWith0Seconds(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 0,
                timerId: 'test1',
                callback: function () use (&$called): void {
                    $called = true;
                },
            )
        );

        $this->assertTrue($called);
    }

    public function testSimpleRegisterOneFunctionWithSleep(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        $calledAt = null;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called, &$calledAt): void {
                    $called = true;
                    $calledAt = time();
                },
            )
        );

        $this->assertFalse($called);
        $expectedTime = time() + 3;
        sleep(3);
        $this->assertTrue($called);
        $this->assertLessThan($expectedTime, $calledAt);
    }

    public function testSimpleRegisterOneFunctionWithSignalCalledBefore(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        $calledAt = null;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test1',
                callback: function () use (&$called, &$calledAt): void {
                    $called = true;
                    $calledAt = time();
                },
            )
        );

        $mustBeCalledAt = time() + 3;
        pcntl_alarm(2);

        $this->assertFalse($called);
        $expectedTime = time() + 5;
        sleep(5);
        $this->assertFalse($called);
        sleep(5);
        $this->assertTrue($called);
        $this->assertGreaterThanOrEqual($mustBeCalledAt, $calledAt);
        $this->assertLessThan($expectedTime, $calledAt);
    }

    public function testSimpleRegisterOneFunctionThenUnregister(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called): void {
                    $called = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );

        $this->assertFalse($called);
        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called);
    }

    public function testSimpleRegisterTwoFunction(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called2);
    }

    public function testSimpleRegisterTwoFunctionWithSleepAtFirst(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                    sleep(3);
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called1);
        $this->assertTrue($called2);
    }

    public function testSimpleRegisterTwoFunctionSecondBeforeFirst(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertTrue($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called1);
    }

    public function testSimpleRegisterTwoFunctionAndFirstRemoved(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertTrue($called2);
    }

    public function testRegisterTwoFunctionAndFirstRemovedAndReregister(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertTrue($called2);
        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called1);
        $this->assertTrue($called2);
    }

    public function testRegisterTwoFunctionAndFirstReregisterWithoutUnregister(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 1,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 3,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called1);
        $this->assertTrue($called2);
        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertTrue($called1);
        $this->assertTrue($called2);
    }
}
