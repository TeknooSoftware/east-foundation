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

namespace Teknoo\East\Foundation\Liveness;

use RuntimeException;
use Teknoo\East\Foundation\Liveness\Exception\TimeLimitReachedException;
use Teknoo\East\Foundation\Time\TimerService;

use function set_time_limit;

/**
 * Service to manage timeout behavior and kill operations that take too long. Its behavior is similar to
 * \set_time_limit, but an throwable exception is throwed instead a fatal error. A fallback on \set_time_limit is even
 * defined X seconds (5 by default). The time limit can be disable.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class TimeoutService
{
    /**
     * @var callable
     */
    private $setTimeoutCallable;

    public function __construct(
        private ?TimerService $timer = null,
    ) {
        $this->setTimeoutCallable = set_time_limit(...);
    }

    public function __destruct()
    {
        $this->disable();
    }

    public static function throwException(): never
    {
        throw new TimeLimitReachedException('Error, time limit exceeded');
    }

    public function enable(int $seconds, int $grace = 5): self
    {
        $this->timer?->register(
            seconds: $seconds,
            timerId: static::class,
            callback: self::throwException(...),
        );
        ($this->setTimeoutCallable)($seconds + $grace);

        return $this;
    }

    public function disable(): self
    {
        ($this->setTimeoutCallable)(0);
        $this->timer?->unregister(static::class);

        return $this;
    }
}
