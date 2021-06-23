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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
