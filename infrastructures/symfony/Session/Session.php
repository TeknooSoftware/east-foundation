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

namespace Teknoo\East\FoundationBundle\Session;

use DomainException;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;

/**
 * Symfony session wrapper, following the SessionInterface of this library.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Session implements SessionInterface
{
    public function __construct(
        private readonly SymfonySession $symfonySession,
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
