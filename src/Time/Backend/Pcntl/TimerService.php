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

namespace Teknoo\East\Foundation\Time\Backend\Pcntl;

use DateTimeInterface;
use Teknoo\East\Foundation\Time\Backend\BackendInterface;
use Teknoo\East\Foundation\Time\Backend\TimersQueueTrait;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\Exception\PcntlNotAvailableException;

use function array_key_first;
use function defined;
use function function_exists;
use function in_array;
use function is_array;
use function key;
use function pcntl_alarm;
use function pcntl_async_signals;
use function pcntl_signal;
use function pcntl_signal_dispatch;
use function pcntl_sigprocmask;

use const PHP_SAPI;
use const SIG_BLOCK;
use const SIG_UNBLOCK;
use const SIGALRM;

/**
 * Simple timer service able to call asyncly a method within X seconds. Several call, at different time can be called.
 * The call is not warranty to be call exactly at X seconds and can be called after (PHP is monothread).
 * A call can be unreferenced before timeout
 * This backend need the pcntl extension to be use, it is not available on Windows OS.
 * It is also not available when the signal SIGALRM is blocked for the current thread, like with FrankenPHP (worker or
 * classic mode, SIGALRM is blocked on all threads), or under the FrankenPHP SAPI.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TimerService implements BackendInterface
{
    use TimersQueueTrait;

    private bool $asyncSignalsEnabled = false;

    private bool $available = false;

    public function __construct(
        private readonly DatesService $datesService,
    ) {
    }

    private function isAlarmSignalBlocked(): bool
    {
        $blockedSignals = [];
        if (!pcntl_sigprocmask(SIG_BLOCK, [SIGALRM], $blockedSignals) || !is_array($blockedSignals)) {
            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        $alreadyBlocked = in_array(SIGALRM, $blockedSignals, true);
        if (!$alreadyBlocked) {
            pcntl_sigprocmask(SIG_UNBLOCK, [SIGALRM]);
        }

        return $alreadyBlocked;
    }

    public function isAvailable(): bool
    {
        //Only a positive result is kept : PHP blocks all signals during the execution of signals handlers, and a
        //callback, executed by the handler, can register a new call (or itself)
        if ($this->available) {
            return true;
        }

        return $this->available = function_exists('pcntl_async_signals')
            && function_exists('pcntl_signal')
            && function_exists('pcntl_alarm')
            && function_exists('pcntl_sigprocmask')
            && function_exists('pcntl_signal_dispatch')
            && defined('SIGALRM')
            && 'frankenphp' !== PHP_SAPI
            && !$this->isAlarmSignalBlocked();
    }

    /**
     * Internal method called when SIGALARM is received
     * @interal
     */
    public function executeCallbacks(): self
    {
        $mustReRun = true;
        while (!empty($this->pipes) && true === $mustReRun) {
            $mustReRun = false;
            $this->datesService->passMeTheDate(
                setter: $this->executeCallsBefore(...),
                preferRealDate: true,
            );

            if (empty($this->pipes)) {
                break;
            }

            $this->datesService->passMeTheDate(
                setter: function (DateTimeInterface $dateTime) use (&$mustReRun): void {
                    $seconds = (key($this->pipes)) - $dateTime->getTimestamp();
                    if ($seconds <= 0) {
                        $mustReRun = true;
                    } else {
                        pcntl_alarm($seconds);
                    }
                },
                preferRealDate: true,
            );
        }

        return $this;
    }

    public function executeExpiredCalls(): self
    {
        //Calls are executed by the SIGALRM handler, dispatch pending signals when async signals are disabled
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }

        return $this;
    }

    public function unregister(string $timerId): self
    {
        $this->removeTimer($timerId);

        return $this;
    }

    public function register(int $seconds, string $timerId, callable $callback): self
    {
        if (!$this->isAvailable()) {
            throw new PcntlNotAvailableException('Pcntl extension is not available or SIGALRM is blocked');
        }

        if (!$this->asyncSignalsEnabled) {
            pcntl_async_signals(true);
            $this->asyncSignalsEnabled = true;
        }

        if (0 === $seconds) {
            $callback();

            return $this;
        }

        $next = null;
        if (!empty($this->pipes)) {
            $next = array_key_first($this->pipes);
        }

        $this->datesService->passMeTheDate(
            setter: function (DateTimeInterface $dateTime) use ($seconds, $timerId, $callback, $next): void {
                $timestamp = (int) ($dateTime->getTimestamp() + $seconds);
                $this->addTimer($timestamp, $timerId, $callback);

                if (null === $next || $next > $timestamp) {
                    pcntl_signal(SIGALRM, $this->executeCallbacks(...));
                    pcntl_alarm($seconds);
                }
            },
            preferRealDate: true,
        );

        return $this;
    }
}
