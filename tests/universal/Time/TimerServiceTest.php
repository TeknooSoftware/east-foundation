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

namespace Teknoo\Tests\East\Foundation\Time;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\TimerService;
use function str_repeat;
use function time;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Time\TimerService
 */
class TimerServiceTest extends TestCase
{
    private ?DatesService $datesService = null;

    public function getDatesServiceMock(): DatesService&MockObject
    {
        if (null === $this->datesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    public function testIsAvailable()
    {
        self::assertTrue(TimerService::isAvailable());
    }

    public function testUnregister()
    {
        self::assertInstanceOf(
            TimerService::class,
            (new TimerService($this->getDatesServiceMock()))->unregister('foo'),
        );
    }

    public function testSimpleRegisterOneFunction()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test1',
                callback: function () use (&$called): void {
                    $called = true;
                },
            )
        );

        self::assertFalse($called);
        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
        self::assertTrue($called);
    }

    public function testSimpleRegisterOneFunctionThenUnregister()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test1',
                callback: function () use (&$called): void {
                    $called = true;
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );

        self::assertFalse($called);
        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
        self::assertFalse($called);
    }

    public function testSimpleRegisterTwoFunction()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        self::assertFalse($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        self::assertTrue($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
        self::assertTrue($called2);
    }

    public function testSimpleRegisterTwoFunctionWithSleepAtFirst()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                    sleep(6);
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        self::assertFalse($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        self::assertTrue($called1);
        self::assertTrue($called2);
    }

    public function testSimpleRegisterTwoFunctionSecondBeforeFirst()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );

        self::assertFalse($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        self::assertFalse($called1);
        self::assertTrue($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
        self::assertTrue($called1);
    }

    public function testSimpleRegisterTwoFunctionAndFirstRemoved()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $service = new TimerService(new DatesService());

        $called1 = false;
        $called2 = false;
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 2,
                timerId: 'test1',
                callback: function () use (&$called1): void {
                    $called1 = true;
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->register(
                seconds: 5,
                timerId: 'test2',
                callback: function () use (&$called2): void {
                    $called2 = true;
                },
            )
        );
        self::assertInstanceOf(
            TimerService::class,
            $service->unregister(
                timerId: 'test1',
            )
        );

        self::assertFalse($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        self::assertFalse($called1);
        self::assertFalse($called2);

        $expectedTime = time() + 3;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }
        self::assertFalse($called1);
        self::assertTrue($called2);
    }
}
