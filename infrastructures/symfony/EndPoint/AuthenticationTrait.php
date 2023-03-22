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

namespace Teknoo\East\FoundationBundle\EndPoint;

use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use function is_callable;
use function is_object;

/**
 * Trait to help developer to write endpoint with Symfony (also called controller) and reuse Symfony components
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait AuthenticationTrait
{
    protected ?TokenStorageInterface $tokenStorage = null;

    /*
     * To inject the Token storage to extract users from session or token.
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @throws LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser(): mixed
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            throw new LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        /*
         * @var TokenInterface
         */
        if (!is_callable($token->getUser(...)) || !is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
