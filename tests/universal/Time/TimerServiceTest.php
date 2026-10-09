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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\Backend\BackendInterface;
use Teknoo\East\Foundation\Time\Exception\NoBackendAvailableException;
use Teknoo\East\Foundation\Time\TimerService;

use function count;
use function is_array;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimerService::class)]
class TimerServiceTest extends TestCase
{
    private function createBackend(
        bool|array $available,
        int $registerCount = 0,
        int $unregisterCount = 0,
        int $executeCount = 0,
    ): BackendInterface&MockObject {
        $backend = $this->createMock(BackendInterface::class);

        if (is_array($available)) {
            $backend->expects($this->exactly(count($available)))
                ->method('isAvailable')
                ->willReturnOnConsecutiveCalls(...$available);
        } else {
            $backend->expects($this->once())
                ->method('isAvailable')
                ->willReturn($available);
        }

        $backend->expects($this->exactly($registerCount))
            ->method('register')
            ->willReturnSelf();

        $backend->expects($this->exactly($unregisterCount))
            ->method('unregister')
            ->willReturnSelf();

        $backend->expects($this->exactly($executeCount))
            ->method('executeExpiredCalls')
            ->willReturnSelf();

        return $backend;
    }

    private function createNeverUsedBackend(): BackendInterface&MockObject
    {
        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->never())->method('isAvailable');
        $backend->expects($this->never())->method('register');
        $backend->expects($this->never())->method('unregister');
        $backend->expects($this->never())->method('executeExpiredCalls');

        return $backend;
    }

    public function testDeprecatedStaticIsAvailable(): void
    {
        $this->assertTrue(TimerService::isAvailable());
    }

    public function testRegisterUseTheFirstAvailableBackend(): void
    {
        $callback = static function (): void {
        };

        $unavailable = $this->createBackend(available: false);
        $available = $this->createBackend(available: true, registerCount: 1);
        $available->expects($this->once())
            ->method('register')
            ->with(5, 'foo', $callback)
            ->willReturnSelf();

        $service = new TimerService(
            $unavailable,
            $available,
            $this->createNeverUsedBackend(),
        );

        $this->assertSame(
            $service,
            $service->register(seconds: 5, timerId: 'foo', callback: $callback),
        );
    }

    public function testUnregisterUseTheFirstAvailableBackend(): void
    {
        $available = $this->createBackend(available: true, unregisterCount: 1);
        $available->expects($this->once())
            ->method('unregister')
            ->with('foo')
            ->willReturnSelf();

        $service = new TimerService(
            $this->createBackend(available: false),
            $available,
            $this->createNeverUsedBackend(),
        );

        $this->assertSame(
            $service,
            $service->unregister('foo'),
        );
    }

    public function testChoiceIsKeptUntilTheDestructionOfTheInstance(): void
    {
        $callback = static function (): void {
        };

        $service = new TimerService(
            $this->createBackend(available: true, registerCount: 2, unregisterCount: 2),
            $this->createNeverUsedBackend(),
        );

        $service->unregister('foo');
        $service->register(seconds: 5, timerId: 'foo', callback: $callback);
        $service->register(seconds: 5, timerId: 'bar', callback: $callback);
        $service->unregister('bar');
    }

    public function testChoiceIsSpecificToEachInstance(): void
    {
        $callback = static function (): void {
        };

        $first = $this->createBackend(available: [false, true], registerCount: 1);
        $second = $this->createBackend(available: true, registerCount: 2);

        $service1 = new TimerService($first, $second);
        $service2 = new TimerService($first, $second);

        $service1->register(seconds: 5, timerId: 'foo', callback: $callback);
        $service1->register(seconds: 5, timerId: 'foo', callback: $callback);
        $service2->register(seconds: 5, timerId: 'foo', callback: $callback);
    }

    public function testRegisterWithoutAvailableBackend(): void
    {
        $service = new TimerService(
            $this->createBackend(available: false),
            $this->createBackend(available: false),
        );

        $this->expectException(NoBackendAvailableException::class);
        $service->register(
            seconds: 5,
            timerId: 'foo',
            callback: static function (): void {
            },
        );
    }

    public function testRegisterWithoutBackend(): void
    {
        $this->expectException(NoBackendAvailableException::class);
        new TimerService()->register(
            seconds: 5,
            timerId: 'foo',
            callback: static function (): void {
            },
        );
    }

    public function testUnregisterWithoutAvailableBackend(): void
    {
        $service = new TimerService(
            $this->createBackend(available: false),
        );

        $this->assertSame(
            $service,
            $service->unregister('foo'),
        );
        $this->assertSame(
            $service,
            $service->unregister('bar'),
        );
    }

    public function testExecuteExpiredCallsWithoutChosenBackend(): void
    {
        $service = new TimerService(
            $this->createNeverUsedBackend(),
        );

        $this->assertSame(
            $service,
            $service->executeExpiredCalls(),
        );
    }

    public function testExecuteExpiredCallsDoesNotPreventTheChoiceOfTheBackend(): void
    {
        $service = new TimerService(
            $this->createBackend(available: false),
            $this->createBackend(available: true, registerCount: 1, executeCount: 2),
            $this->createNeverUsedBackend(),
        );

        $service->executeExpiredCalls();
        $service->register(
            seconds: 5,
            timerId: 'foo',
            callback: static function (): void {
            },
        );

        $this->assertSame(
            $service,
            $service->executeExpiredCalls(),
        );
        $service->executeExpiredCalls();
    }

    public function testExecuteExpiredCallsWithoutAvailableBackend(): void
    {
        $service = new TimerService(
            $this->createBackend(available: false),
        );

        $service->unregister('foo');
        $this->assertSame(
            $service,
            $service->executeExpiredCalls(),
        );
    }
}
