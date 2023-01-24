<?php

/*
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

namespace Teknoo\East\Foundation\Time;

use DateTimeInterface;
use RuntimeException;

use function array_shift;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class TimerService
{
    /**
     * @var array<string, callable>
     */
    private array $callbacks = [];

    /**
     * @var array<int, array<int, string>>
     */
    private array $pipe = [];

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

    /**
     * Internal method called when SIGALARM is received
     * @interal
     */
    public function executeCallbacks(): self
    {
        $mustReRun = true;
        while (!empty($this->pipe) && true === $mustReRun) {
            $mustReRun = false;
            $this->datesService->passMeTheDate(
                setter: function (DateTimeInterface $dateTime): void {
                    $timestamp = $dateTime->getTimestamp();
                    while (false !== ($timersIds = current($this->pipe)) && key($this->pipe) <= $timestamp) {
                        unset($this->pipe[key($this->pipe)]);
                        foreach ($timersIds as $timerId) {
                            if (!isset($this->callbacks[$timerId])) {
                                continue;
                            }

                            $callback = $this->callbacks[$timerId];
                            unset($this->callbacks[$timerId]);
                            $callback();
                            unset($callback);
                        }
                    }
                },
                preferRealDate: true,
            );

            if (empty($this->pipe)) {
                break;
            }

            $this->datesService->passMeTheDate(
                setter: function (DateTimeInterface $dateTime) use (&$mustReRun): void {
                    $seconds = (key($this->pipe)) - $dateTime->getTimestamp();
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

        return $this;
    }

    public function register(int $seconds, string $timerId, callable $callback): self
    {
        if (!self::isAvailable()) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Pcntl extension is not available');
            // @codeCoverageIgnoreEnd
        }

        $next = null;
        if (!empty($this->pipe)) {
            reset($this->pipe);
            $next = key($this->pipe);
        }

        $this->callbacks[$timerId] = $callback;

        $this->datesService->passMeTheDate(
            setter: function (DateTimeInterface $dateTime) use ($seconds, $timerId, $next): void {
                $timestamp = (int) ($dateTime->getTimestamp() + $seconds);
                $this->pipe[$timestamp][] = $timerId;
                ksort($this->pipe);

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
