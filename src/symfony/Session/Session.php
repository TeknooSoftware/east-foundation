<?php

declare(strict_types=1);

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\FoundationBundle\Session;

use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;

/**
 * Symfony session wrapper, following the SessionInterface of this library.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Session implements SessionInterface
{
    /**
     * @var SymfonySession
     */
    private $symfonySession;

    /**
     * Session constructor.
     * @param SymfonySession $symfonySession
     */
    public function __construct(SymfonySession $symfonySession)
    {
        $this->symfonySession = $symfonySession;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): SessionInterface
    {
        $this->symfonySession->set($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, PromiseInterface $promise): SessionInterface
    {
        if ($this->symfonySession->has($key)) {
            $promise->success($this->symfonySession->get($key));
        } else {
            $promise->fail(new \DomainException("%key is not available"));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $key): SessionInterface
    {
        $this->symfonySession->remove($key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): SessionInterface
    {
        $this->symfonySession->clear();

        return $this;
    }
}
