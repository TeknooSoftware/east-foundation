<?php

/**
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
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Session;

use DomainException;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;

/**
 * Symfony session wrapper, following the SessionInterface of this library.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Session implements SessionInterface
{
    public function __construct(
        private SymfonySession $symfonySession,
    ) {
    }

    public function set(string $key, mixed $value): SessionInterface
    {
        $this->symfonySession->set($key, $value);

        return $this;
    }

    /**
     * @param PromiseInterface<mixed, mixed> $promise
     */
    public function get(string $key, PromiseInterface $promise): SessionInterface
    {
        if ($this->symfonySession->has($key)) {
            $promise->success($this->symfonySession->get($key));
        } else {
            $promise->fail(new DomainException("%key is not available"));
        }

        return $this;
    }

    public function remove(string $key): SessionInterface
    {
        $this->symfonySession->remove($key);

        return $this;
    }

    public function clear(): SessionInterface
    {
        $this->symfonySession->clear();

        return $this;
    }
}
