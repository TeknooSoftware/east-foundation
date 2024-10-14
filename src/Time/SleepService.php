<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Time;

use Random\RandomException;
use SensitiveParameter;
use Teknoo\East\Foundation\Time\Exception\PcntlNotAvailableException;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function bin2hex;
use function function_exists;
use function pcntl_signal_dispatch;
use function random_bytes;
use function usleep;

/**
 * Service to perform sleeping operations to sleep without blocking other async events
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SleepService implements SleepServiceInterface
{
    public function __construct(
        private TimerServiceInterface $timer,
        private int $usleeepTime = 1000,
    ) {
    }

    private static function isAvailable(): bool
    {
        return function_exists('pcntl_signal_dispatch');
    }

    /**
     * @throws RandomException
     */
    public function wait(int $seconds): SleepServiceInterface
    {
        if (!self::isAvailable()) {
            // @codeCoverageIgnoreStart
            throw new PcntlNotAvailableException('Pcntl extension is not available');
            // @codeCoverageIgnoreEnd
        }

        $timerId = "timer-$seconds" . bin2hex(random_bytes(23));

        $timerFinished = new Promise(
            fn () => true,
            fn (#[SensitiveParameter] Throwable $error) => throw $error,
        );
        $timerFinished->setDefaultResult(false);

        $this->timer->register(
            seconds: $seconds,
            timerId: $timerId,
            callback: $timerFinished,
        );

        while (!$timerFinished->fetchResult()) {
            usleep($this->usleeepTime);
            pcntl_signal_dispatch();
        }

        return $this;
    }
}
