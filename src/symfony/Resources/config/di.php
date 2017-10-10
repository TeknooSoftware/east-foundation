<?php
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

namespace Teknoo\East\FoundationBundle\Resources\config;

use function DI\get;
use function DI\decorate;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\FoundationBundle\Http\Client;
use Teknoo\East\FoundationBundle\Router\Router;
use Teknoo\East\FoundationBundle\Session\SessionMiddleware;

return [
    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => function (LoggerInterface $logger): ProcessorInterface {
        return new Processor($logger);
    },

    Router::class => get(RouterInterface::class),
    RouterInterface::class => function (UrlMatcherInterface $urlMatcher, ContainerInterface $container): RouterInterface {
        return new Router($urlMatcher, $container);
    },

    SessionMiddleware::class => function (): SessionMiddleware {
        return new SessionMiddleware();
    },

    Client::class => get(ClientInterface::class),
    ClientInterface::class => function (HttpFoundationFactory $factory): ClientInterface {
        return new Client($factory);
    },

    ManagerInterface::class => decorate(function ($previous, ContainerInterface $container) {
        if ($previous instanceof ManagerInterface) {
            $previous->registerMiddleware($container->get(SessionMiddleware::class, 5));
            $previous->registerMiddleware($container->get(RouterInterface::class, 10));
            $previous->registerMiddleware($container->get(ProcessorInterface::class, 15));
        }

        return $previous;
    })
];
