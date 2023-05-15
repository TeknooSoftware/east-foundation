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

namespace Teknoo\East\FoundationBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Client\ClientInterface as BaseClient;
use Teknoo\East\Foundation\Http\ClientInterface as HttpClient;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\FoundationBundle\Http\Client;
use Teknoo\East\FoundationBundle\Session\SessionMiddleware;

use function DI\get;
use function DI\decorate;
use function DI\create;

return [
    SessionMiddleware::class => create(SessionMiddleware::class),

    BaseClient::class => get(Client::class),
    HttpClient::class => get(Client::class),

    RecipeInterface::class => decorate(static function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->registerMiddleware(
                $container->get(SessionMiddleware::class),
                SessionMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    })
];
