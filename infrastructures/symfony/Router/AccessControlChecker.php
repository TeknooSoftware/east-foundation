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

namespace Teknoo\East\FoundationBundle\Router;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Firewall\FirewallListenerInterface;

/**
 * Access checker asking the firewall if the page displaying a Symfony UX Live Component (the route targeted by its
 * `originalPath`) is authorized : as Symfony UX does for its sub-requests, the current Symfony request (the attribute
 * `request` of the PSR request) is duplicated for the path of this page, with the method of the Live Component request
 * and the routing attributes of its route, and passed to the access listener of the firewall
 * (`security.access_listener`). A refused access throws its
 * `AccessDeniedException` : Symfony returns a 403 response, or starts the authentication when the user is not
 * authenticated.
 *
 * The firewall runs before East Foundation's kernel listener, so the token of the current user is available.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccessControlChecker implements AccessCheckerInterface
{
    public function __construct(
        private readonly FirewallListenerInterface $accessListener,
        private readonly HttpKernelInterface $kernel,
    ) {
    }

    public function check(ServerRequestInterface $request, string $path, array $parameters): AccessCheckerInterface
    {
        $currentRequest = $request->getAttribute('request');
        if (!$currentRequest instanceof Request) {
            throw new AccessDeniedException("The access to the path '$path' can not be checked without request");
        }

        $subRequest = $currentRequest->duplicate(
            query: [],
            request: [],
            attributes: $parameters,
            server: [
                'REQUEST_URI' => $currentRequest->getBaseUrl() . $path,
                'QUERY_STRING' => '',
            ] + $currentRequest->server->all(),
        );

        if ($this->accessListener->supports($subRequest)) {
            $this->accessListener->authenticate(
                new RequestEvent(
                    $this->kernel,
                    $subRequest,
                    HttpKernelInterface::SUB_REQUEST,
                ),
            );
        }

        return $this;
    }
}
