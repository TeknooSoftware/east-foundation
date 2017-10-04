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

namespace Teknoo\East\FoundationBundle\Router;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\RouterInterface;

/**
 * Class Router to check if a request is runnable by one of its controller and pass it to the selected controller.
 * This router reuse the Symfony matcher component to find controller and routes to use. Only controller as service
 * (The matcher returns a callable and not the controller's identifier Controller::Action). If the controller is not
 * a callable, this router ignores the route.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Router implements RouterInterface
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Router constructor.
     *
     * @param UrlMatcherInterface $urlMatcher
     * @param ContainerInterface  $container
     */
    public function __construct(
        UrlMatcherInterface $urlMatcher,
        ContainerInterface $container
    ) {
        $this->matcher = $urlMatcher;
        $this->container = $container;
    }

    /**
     * Method to find the controller to call for this method via the Symfony Matcher. Return only controller as service
     * (callable provided by the Symfony matcher), ignore other.
     *
     * @param ServerRequestInterface $request
     *
     * @return callable
     */
    private function matchRequest(ServerRequestInterface $request)
    {
        try {
            $parameters = $this->matcher->match(
                \str_replace(
                    ['/app.php', '/app_dev.php'],
                    '',
                    $request->getUri()->getPath()
                )
            );
        } catch (ResourceNotFoundException $e) {
            /* Do nothing, keep the framework to manage it */
        }

        if (empty($parameters['_controller'])) {
            return null;
        }

        if (\is_callable($parameters['_controller'])) {
            if (\is_string($parameters['_controller'])
                && false !== \strpos($parameters['_controller'], '::')) {
                return \explode('::', $parameters['_controller']);
            }

            return $parameters['_controller'];
        }

        if (!$this->container->has($parameters['_controller'])) {
            return null;
        }

        /**
         * @var callable
         */
        $entry = $this->container->get($parameters['_controller']);

        if (\is_callable($entry)) {
            return $entry;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        $controller = $this->matchRequest($request);

        if (\is_callable($controller)) {
            $result = new Result($controller);
            $request = $request->withAttribute(RouterInterface::ROUTER_RESULT_KEY, $result);

            $manager->continueExecution($client, $request);
        }

        return $this;
    }
}
