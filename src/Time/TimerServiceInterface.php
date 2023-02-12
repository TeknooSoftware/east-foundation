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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface TimerServiceInterface
{
    public function unregister(string $timerId): TimerServiceInterface;

    public function register(int $seconds, string $timerId, callable $callback): TimerServiceInterface;
}
