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
use Throwable;

/**
 * Contract to check, before the router re-runs the recipe of the route targeted by a Symfony UX Live Component
 * request (its `originalPath` prop), that the current user can access to this route. The Symfony firewall checks only
 * the path of the Live Component request (`/_components/...`), never the path of the targeted route.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface AccessCheckerInterface
{
    /**
     * @param ServerRequestInterface $request the Live Component request
     * @param string $path the path of the targeted route, without front controller and query string
     * @param array<string, mixed> $parameters the routing parameters matched for this path
     *
     * @throws Throwable when the current user can not access to the path
     */
    public function check(ServerRequestInterface $request, string $path, array $parameters): AccessCheckerInterface;
}
