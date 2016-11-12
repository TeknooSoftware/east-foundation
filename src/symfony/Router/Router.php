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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
namespace Teknoo\East\FoundationBundle\Router;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\RouterInterface;

/**
 * Class Router to check if a request is runnable by one of its controller and pass it to the selected controller.
 * This router reuse the Symfony matcher component to find controller and routes to use.
 *
 * The router can stop the propagation in the manager by calling stopPropagation.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
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
     * @var ProcessorInterface
     */
    private $processor;

    /**self
     * Router constructor.
     * @param UrlMatcherInterface $urlMatcher
     * @param ProcessorInterface $processor
     */
    public function __construct(
        UrlMatcherInterface $urlMatcher,
        ProcessorInterface $processor
    ) {
        $this->matcher = $urlMatcher;
        $this->processor = $processor;
    }

    /**
     * Method to find the controller to call for this method via the Symfony Matcher.
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

            if (isset($parameters['_controller']) && \is_callable($parameters['_controller'])) {
                return $parameters['_controller'];
            }
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function receiveRequestFromServer(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): RouterInterface {
        $controller = $this->matchRequest($request);

        if (\is_callable($controller)) {
            $result = new Result($controller);
            $this->processor->executeRequest($client, $request, $result);

            $manager->stopPropagation();
        }

        return $this;
    }
}
