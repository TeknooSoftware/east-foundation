<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\East\Foundation\Time;

use DateTimeInterface;
use Teknoo\East\Foundation\Time\Exception\PcntlNotAvailableException;

use function array_diff;
use function current;
use function function_exists;
use function key;
use function ksort;
use function pcntl_alarm;
use function pcntl_async_signals;
use function pcntl_signal;
use function reset;

use const SIGALRM;

/**
 * Simple timer service able to call asyncly a method within X seconds. Several call, at different time can be called.
 * The call is not warranty to be call exactly at X seconds and can be called after (PHP is monothread).
 * A call can be unreferenced before timeout
 * This service need the pcntl extension to be use, it is not available on Windows OS.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TimerService implements TimerServiceInterface
{
    /**
     * @var array<string, callable>
     */
    private array $callbacks = [];

    /**
     * @var array<int, array<int, string>>
     */
    private array $pipes = [];

    public function __construct(
        private DatesService $datesService,
    ) {
        pcntl_async_signals(true);
    }

    public static function isAvailable(): bool
    {
        return function_exists('pcntl_async_signals')
            && function_exists('pcntl_signal')
            && function_exists('pcntl_alarm');
    }

    public function executeCallsBefore(DateTimeInterface $dateTime): void
    {
        $timestamp = $dateTime->getTimestamp();
        while (false !== ($timersIds = current($this->pipes)) && key($this->pipes) <= $timestamp) {
            unset($this->pipes[key($this->pipes)]);
            foreach ($timersIds as $timerId) {
                if (isset($this->callbacks[$timerId])) {
                    $callback = $this->callbacks[$timerId];
                    unset($this->callbacks[$timerId]);
                    $callback();
                    unset($callback);
                }
            }
        }
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

    public function unregister(string $timerId): self
    {
        if (isset($this->callbacks[$timerId])) {
            unset($this->callbacks[$timerId]);
        }

        foreach ($this->pipes as &$pipe) {
            $pipe = array_diff($pipe, [$timerId]);
        }

        return $this;
    }

    public function register(int $seconds, string $timerId, callable $callback): self
    {
        if (!self::isAvailable()) {
            // @codeCoverageIgnoreStart
            throw new PcntlNotAvailableException('Pcntl extension is not available');
            // @codeCoverageIgnoreEnd
        }

        $next = null;
        if (!empty($this->pipes)) {
            reset($this->pipes);
            $next = key($this->pipes);
        }

        if (isset($this->callbacks[$timerId])) {
            $this->unregister($timerId);
        }

        $this->callbacks[$timerId] = $callback;

        $this->datesService->passMeTheDate(
            setter: function (DateTimeInterface $dateTime) use ($seconds, $timerId, $next): void {
                $timestamp = (int) ($dateTime->getTimestamp() + $seconds);
                $this->pipes[$timestamp][] = $timerId;
                ksort($this->pipes);

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
