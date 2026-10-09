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

namespace Teknoo\East\Foundation\Time\Backend;

use DateTimeInterface;

use function array_diff;
use function current;
use function key;
use function ksort;
use function reset;

/**
 * Trait to share, between timer's backends, the queue of timers to call, sorted by timestamp, and the logic to execute
 * all timers expected before a date. A callback is removed from the queue before being called, so a callback can
 * register again itself without be called twice.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait TimersQueueTrait
{
    /**
     * @var array<string, callable>
     */
    private array $callbacks = [];

    /**
     * @var array<int, array<int, string>>
     */
    private array $pipes = [];

    /**
     * Internal method to execute all timers expected before the passed date
     * @internal
     */
    public function executeCallsBefore(DateTimeInterface $dateTime): void
    {
        $timestamp = $dateTime->getTimestamp();
        reset($this->pipes);
        while (false !== ($timersIds = current($this->pipes)) && key($this->pipes) <= $timestamp) {
            if ($key = key($this->pipes)) {
                unset($this->pipes[$key]);
            }

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

    private function removeTimer(string $timerId): void
    {
        if (isset($this->callbacks[$timerId])) {
            unset($this->callbacks[$timerId]);
        }

        foreach ($this->pipes as &$pipe) {
            $pipe = array_diff($pipe, [$timerId]);
        }
    }

    private function addTimer(int $timestamp, string $timerId, callable $callback): void
    {
        if (isset($this->callbacks[$timerId])) {
            $this->removeTimer($timerId);
        }

        $this->callbacks[$timerId] = $callback;
        $this->pipes[$timestamp][] = $timerId;
        ksort($this->pipes);
    }
}
