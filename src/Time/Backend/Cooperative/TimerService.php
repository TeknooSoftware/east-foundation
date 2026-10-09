<?php

/*
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

namespace Teknoo\East\Foundation\Time\Backend\Cooperative;

use Closure;
use DateTimeInterface;
use Teknoo\East\Foundation\Time\Backend\BackendInterface;
use Teknoo\East\Foundation\Time\Backend\TimersQueueTrait;
use Teknoo\East\Foundation\Time\DatesService;
use WeakReference;

use function array_key_first;
use function register_tick_function;
use function time;
use function unregister_tick_function;

/**
 * Cooperative timer service, without any extension, available everywhere, like FrankenPHP in worker mode, where the
 * pcntl extension is not functional. Several call, at different time can be called.
 * PHP can not interrupt the current execution without signal, so expired calls are executed only at checkpoints :
 * - on each call to `register` (before registering the new call),
 * - on each tick, thanks to a tick function registered by this service. Ticks are only emitted by the code of files
 *   declaring `declare(ticks=N);`, like `Teknoo\East\Foundation\Time\SleepService`. A developer can declare ticks in
 *   its own files to have expired calls executed during its long operations (an exception throwed by a call is
 *   propagated into the interrupted code).
 * The call is not warranty to be call exactly at X seconds and can be called after, nothing will be executed during a
 * blocking operation (sleep, IO, SQL query, etc..) or in a code without ticks.
 * `unregister` never executes expired calls, it can be safely used in a destructor.
 * A call can be unreferenced before timeout
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TimerService implements BackendInterface
{
    use TimersQueueTrait;

    private ?Closure $tickFunction = null;

    private ?int $nextTimestamp = null;

    private bool $isExecuting = false;

    public function __construct(
        private readonly DatesService $datesService,
    ) {
    }

    public function __destruct()
    {
        if (null !== $this->tickFunction) {
            unregister_tick_function($this->tickFunction);
            $this->tickFunction = null;
        }
    }

    public function isAvailable(): bool
    {
        return true;
    }

    private function registerTickFunction(): void
    {
        if (null !== $this->tickFunction) {
            return;
        }

        //Weak reference to not keep this service in memory only because the tick function is registered
        $reference = WeakReference::create($this);
        $this->tickFunction = static function () use ($reference): void {
            $reference->get()?->executeExpiredCallbacks();
        };

        register_tick_function($this->tickFunction);
    }

    private function updateNextTimestamp(): void
    {
        $this->nextTimestamp = array_key_first($this->pipes);
    }

    private function executeExpiredCallbacks(): void
    {
        if ($this->isExecuting || null === $this->nextTimestamp || time() < $this->nextTimestamp) {
            return;
        }

        $this->isExecuting = true;
        try {
            $this->datesService->passMeTheDate(
                setter: $this->executeCallsBefore(...),
                preferRealDate: true,
            );
        } finally {
            $this->isExecuting = false;
            $this->updateNextTimestamp();
        }
    }

    public function unregister(string $timerId): self
    {
        $this->removeTimer($timerId);
        $this->updateNextTimestamp();

        return $this;
    }

    public function register(int $seconds, string $timerId, callable $callback): self
    {
        if (0 === $seconds) {
            $callback();

            return $this;
        }

        $this->executeExpiredCallbacks();
        $this->registerTickFunction();

        $this->datesService->passMeTheDate(
            setter: function (DateTimeInterface $dateTime) use ($seconds, $timerId, $callback): void {
                $this->addTimer((int) ($dateTime->getTimestamp() + $seconds), $timerId, $callback);
            },
            preferRealDate: true,
        );

        $this->updateNextTimestamp();

        return $this;
    }
}
