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

use Teknoo\East\Foundation\Time\Backend\BackendInterface;
use Teknoo\East\Foundation\Time\Exception\NoBackendAvailableException;

/**
 * Simple timer service able to call asyncly a method within X seconds. Several call, at different time can be called.
 * The call is not warranty to be call exactly at X seconds and can be called after (PHP is monothread).
 * A call can be unreferenced before timeout
 * This service is a frontal service, it delegates calls to the first available backend (in the order of the list passed
 * to the constructor), like :
 * - `Teknoo\East\Foundation\Time\Backend\Pcntl\TimerService`, built on the pcntl extension and SIGALRM signal,
 * - `Teknoo\East\Foundation\Time\Backend\Cooperative\TimerService`, available everywhere, like FrankenPHP in worker
 *    mode, but calls are executed only at some checkpoints (ticks, new registration).
 * The backend is chosen at the first call of `register` or `unregister` and kept until the destruction of this
 * instance. Each instance does its own choice.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TimerService implements TimerServiceInterface
{
    /**
     * @var array<int|string, BackendInterface>
     */
    private readonly array $backends;

    private ?BackendInterface $backend = null;

    private bool $backendSelected = false;

    public function __construct(BackendInterface ...$backends)
    {
        $this->backends = $backends;
    }

    /**
     * @deprecated since 9.3.0, with the default configuration, a backend is always available, thanks to the
     *  cooperative backend. Availability is now checked by each backend.
     */
    public static function isAvailable(): bool
    {
        return true;
    }

    private function getBackend(): ?BackendInterface
    {
        if (!$this->backendSelected) {
            $this->backendSelected = true;

            foreach ($this->backends as $backend) {
                if ($backend->isAvailable()) {
                    $this->backend = $backend;

                    break;
                }
            }
        }

        return $this->backend;
    }

    public function unregister(string $timerId): self
    {
        //Without available backend, no call can be registered, so there is nothing to unregister
        $this->getBackend()?->unregister($timerId);

        return $this;
    }

    public function register(int $seconds, string $timerId, callable $callback): self
    {
        $backend = $this->getBackend();
        if (null === $backend) {
            throw new NoBackendAvailableException('Error, no timer backend is available in this environment');
        }

        $backend->register(
            seconds: $seconds,
            timerId: $timerId,
            callback: $callback,
        );

        return $this;
    }
}
