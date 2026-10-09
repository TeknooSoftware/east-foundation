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

namespace Teknoo\Tests\East\Foundation\Time\Backend\Cooperative;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\Backend\Cooperative\TimerService;
use Teknoo\East\Foundation\Time\Backend\TimersQueueTrait;
use Teknoo\East\Foundation\Time\DatesService;

use function sleep;
use function str_repeat;
use function time;

/**
 * Tests about checkpoints of the cooperative timer in a code without ticks (this file does not declare ticks).
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimerService::class)]
#[CoversTrait(TimersQueueTrait::class)]
class TimerServiceWithoutTicksTest extends TestCase
{
    public function testExpiredCallsAreNotExecutedWithoutTicks(): void
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

        $expectedTime = time() + 2;
        while (time() < $expectedTime) {
            $x = str_repeat('x', 100000);
        }

        $this->assertFalse($called);
    }

    public function testRegisterExecutesExpiredCalls(): void
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

        sleep(2);
        $this->assertFalse($called1);

        $service->register(
            seconds: 5,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );

        $this->assertTrue($called1);
        $this->assertFalse($called2);
    }

    public function testUnregisterDoesNotExecuteExpiredCalls(): void
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
            seconds: 1,
            timerId: 'test2',
            callback: function () use (&$called2): void {
                $called2 = true;
            },
        );

        sleep(2);
        $service->unregister('test2');
        $this->assertFalse($called1);
        $this->assertFalse($called2);

        $service->register(
            seconds: 5,
            timerId: 'test3',
            callback: static function (): void {
            },
        );

        $this->assertTrue($called1);
        $this->assertFalse($called2);
    }

    public function testExecuteExpiredCalls(): void
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

        $this->assertSame(
            $service,
            $service->executeExpiredCalls(),
        );
        $this->assertFalse($called);

        sleep(2);
        $this->assertFalse($called);

        $this->assertSame(
            $service,
            $service->executeExpiredCalls(),
        );
        $this->assertTrue($called);
    }
}
