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

//Ticks are required in this file to allow the cooperative timer to execute expired calls during busy loops
declare(ticks=1);

namespace Teknoo\Tests\East\Foundation\Time\Backend\Cooperative;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Time\Backend\Cooperative\TimerService;
use Teknoo\East\Foundation\Time\Backend\TimersQueueTrait;
use Teknoo\East\Foundation\Time\DatesService;

use function sleep;
use function str_repeat;
use function time;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimerService::class)]
#[CoversTrait(TimersQueueTrait::class)]
class TimerServiceTest extends TestCase
{
    private function getDatesServiceStub(): DatesService&Stub
    {
        return $this->createStub(DatesService::class);
    }

    private function busyWait(int $seconds): void
    {
        $expectedTime = time() + $seconds;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
    }

    public function testIsAvailable(): void
    {
        $this->assertTrue(new TimerService($this->getDatesServiceStub())->isAvailable());
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
        $this->busyWait(2);
        $this->assertTrue($called);
    }

    public function testSimpleRegisterOneFunctionWith0Seconds(): void
    {
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
        $service = new TimerService(new DatesService());

        $called = false;
        $calledAt = null;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called, &$calledAt): void {
                $called = true;
                $calledAt = time();
            },
        );

        $mustBeCalledAfter = time() + 1;
        //sleep is blocking, the call can be executed only at the next tick, after the sleep
        sleep(2);
        $this->assertTrue($called);
        $this->assertGreaterThanOrEqual($mustBeCalledAfter, $calledAt);
    }

    public function testSimpleRegisterOneFunctionThenUnregister(): void
    {
        $service = new TimerService(new DatesService());

        $called = false;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called): void {
                $called = true;
            },
        );
        $this->assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );

        $this->busyWait(2);
        $this->assertFalse($called);
    }

    public function testSimpleRegisterTwoFunction(): void
    {
        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called1): void {
                $called1 = true;
            },
        );
        $service->register(
            seconds: 3,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );

        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $this->busyWait(2);
        $this->assertTrue($called1);
        $this->assertFalse($called2);

        $this->busyWait(2);
        $this->assertTrue($called2);
    }

    public function testSimpleRegisterTwoFunctionSecondBeforeFirst(): void
    {
        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $service->register(
            seconds: 3,
            timerId: 'test1',
            callback: function () use (&$called1): void {
                $called1 = true;
            },
        );
        $service->register(
            seconds: 1,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );

        $this->busyWait(2);
        $this->assertFalse($called1);
        $this->assertTrue($called2);

        $this->busyWait(2);
        $this->assertTrue($called1);
    }

    public function testRegisterTwoFunctionAndFirstReregisterWithoutUnregister(): void
    {
        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called1): void {
                $called1 = true;
            },
        );
        $service->register(
            seconds: 3,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );
        $service->register(
            seconds: 5,
            timerId: 'test1',
            callback: function () use (&$called1): void {
                $called1 = true;
            },
        );

        $this->busyWait(2);
        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $this->busyWait(2);
        $this->assertFalse($called1);
        $this->assertTrue($called2);

        $this->busyWait(2);
        $this->assertTrue($called1);
    }

    public function testCallbackRegisteringItselfAgain(): void
    {
        $service = new TimerService(new DatesService());

        $counter = 0;
        $callback = null;
        $callback = function () use ($service, &$counter, &$callback): void {
            ++$counter;
            $service->register(
                seconds: 1,
                timerId: 'loop',
                callback: $callback,
            );
        };

        $service->register(
            seconds: 1,
            timerId: 'loop',
            callback: $callback,
        );

        $this->busyWait(4);
        $service->unregister('loop');

        $this->assertGreaterThanOrEqual(3, $counter);
        $this->assertLessThanOrEqual(4, $counter);
    }

    public function testExceptionThrownByCallbackIsPropagatedIntoTheRunningCode(): void
    {
        $service = new TimerService(new DatesService());

        $service->register(
            seconds: 1,
            timerId: 'timeout',
            callback: static function (): never {
                throw new RuntimeException('Time limit reached');
            },
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Time limit reached');
        $this->busyWait(3);
    }

    public function testNextCallsAreStillExecutedAfterAnExceptionThrownByACallback(): void
    {
        $service = new TimerService(new DatesService());

        $called = false;
        $service->register(
            seconds: 1,
            timerId: 'timeout',
            callback: static function (): never {
                throw new RuntimeException('Time limit reached');
            },
        );
        $service->register(
            seconds: 2,
            timerId: 'test2',
            callback: function () use (&$called): void {
                $called = true;
            },
        );

        $error = null;
        try {
            $this->busyWait(3);
        } catch (RuntimeException $error) {
        }

        $this->assertInstanceOf(RuntimeException::class, $error);

        $this->busyWait(2);
        $this->assertTrue($called);
    }

    public function testExpiredCallsAreNotExecutedRecursivelyDuringACall(): void
    {
        $service = new TimerService(new DatesService());

        $called2 = false;
        $called2DuringCall1 = null;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called2, &$called2DuringCall1): void {
                //Ticks are emitted during this busy wait, the second timer expires, but must wait the end of this call
                $this->busyWait(2);
                $called2DuringCall1 = $called2;
            },
        );
        $service->register(
            seconds: 2,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );

        $this->busyWait(4);
        $this->assertFalse($called2DuringCall1);
        $this->assertTrue($called2);
    }

    public function testCallsAreNotExecutedAfterServiceDestruction(): void
    {
        $service = new TimerService(new DatesService());

        $called = false;
        $service->register(
            seconds: 1,
            timerId: 'test1',
            callback: function () use (&$called): void {
                $called = true;
            },
        );

        unset($service);

        $this->busyWait(2);
        $this->assertFalse($called);
    }
}
