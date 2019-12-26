<?php

/*
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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\FoundationBundle\Http\Client;
use Teknoo\East\FoundationBundle\Session\SessionMiddleware;

use function DI\get;
use function DI\decorate;
use function DI\create;

return [
    SessionMiddleware::class => create(SessionMiddleware::class),

    Client::class => get(ClientInterface::class),
    ClientInterface::class => create(Client::class)
        ->constructor(get(HttpFoundationFactory::class)),

    RecipeInterface::class => decorate(function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->registerMiddleware(
                $container->get(SessionMiddleware::class),
                SessionMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    })
];
