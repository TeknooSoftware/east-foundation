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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Liveness;

/**
 * Service to centralize all pings operations to call all in a single method call. Any ping operations can be
 * added and removed dynamicly. A ping operation is mandatory identified by an id.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PingService implements PingServiceInterface
{
    /**
     * @var array<string, callable>
     */
    private array $callbacks = [];

    public function register(string $id, callable $callback): PingServiceInterface
    {
        $this->callbacks[$id] = $callback;

        return $this;
    }

    public function unregister(string $id): PingServiceInterface
    {
        if (isset($this->callbacks[$id])) {
            unset($this->callbacks[$id]);
        }

        return $this;
    }

    public function ping(): PingServiceInterface
    {
        foreach ($this->callbacks as $callback) {
            $callback();
        }

        return $this;
    }
}
