<?php
/**
 * East Framework.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Framework\Router;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Teknoo\East\Framework\Processor\ProcessorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

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
     * Method to find the controller to call for this method via the Symfony Matcher
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    private function matchRequest(ServerRequestInterface $request): array
    {
        try {
            return $this->matcher->match(
                str_replace('/app_dev.php', '', $request->getUri()->getPath())
            );
        } catch(ResourceNotFoundException $e) {
            return [];
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
        $parameters = $this->matchRequest($request);

        if (!empty($parameters)) {
            $this->processor->executeRequest($client, $request, $parameters);

            $manager->stopPropagation();
        }

        return $this;
    }
}
