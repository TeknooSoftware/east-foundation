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

namespace Teknoo\East\FoundationBundle\Session;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Session\SessionInterface;

/**
 * Middle to inject into request's attributes the current session instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SessionMiddleware implements MiddlewareInterface
{
    final public const MIDDLEWARE_PRIORITY = 5;

    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager
    ): MiddlewareInterface {
        if (!$message instanceof ServerRequestInterface) {
            return $this;
        }

        if (($requestSf = $message->getAttribute('request')) instanceof Request) {
            $session = new Session($requestSf->getSession());
            $request = $message->withAttribute(SessionInterface::ATTRIBUTE_KEY, $session);

            $manager->updateMessage($request);
            $manager->updateWorkPlan([
                SessionInterface::class => $session,
            ]);
        }

        return $this;
    }
}
