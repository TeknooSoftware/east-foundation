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

namespace Teknoo\East\Foundation\Time;

use Random\RandomException;
use SensitiveParameter;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function bin2hex;
use function random_bytes;
use function usleep;

/**
 * Service to perform sleeping operations to sleep without blocking other async events
 * During the waiting, the timer is regularly asked to execute expired calls, thanks to
 * `TimerServiceInterface::executeExpiredCalls()`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SleepService implements SleepServiceInterface
{
    public function __construct(
        private readonly TimerServiceInterface $timer,
        private readonly int $usleeepTime = 1000,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function wait(int $seconds): SleepServiceInterface
    {
        $timerId = "timer-$seconds" . bin2hex(random_bytes(23));

        $timerFinished = new Promise(
            fn (): true => true,
            fn (#[SensitiveParameter] Throwable $error) => throw $error,
        );
        $timerFinished->allowReuse();
        $timerFinished->setDefaultResult(false);

        $this->timer->register(
            seconds: $seconds,
            timerId: $timerId,
            callback: $timerFinished,
        );

        while (!$timerFinished->fetchResult()) {
            usleep($this->usleeepTime);
            $this->timer->executeExpiredCalls();
        }

        return $this;
    }
}
