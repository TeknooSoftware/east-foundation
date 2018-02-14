<?php

declare(strict_types=1);

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

namespace Teknoo\East\Foundation\Processor;

use Teknoo\East\Foundation\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Bowl\Bowl;

/**
 * Processor implementation to call each controller callable returned by the
 * router the PSR7 Server Request, the ClientInterface instance and other callable's argument founded in the request.
 *
 * If some arguments are missing in the request. The processor must throws exceptions.
 *
 * The Processor is independent of Symfony.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Processor implements ProcessorInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        $processor = clone $this;

        $routerResult = $request->getAttribute(RouterInterface::ROUTER_RESULT_KEY);
        if ($routerResult instanceof ResultInterface) {
            $processor->doExecute($client, $request, $routerResult, $manager);
        }

        return $processor;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getParameters(ServerRequestInterface $request): array
    {
        return \array_merge(
            (array) $request->getQueryParams(),
            (array) $request->getParsedBody(),
            (array) $request->getAttributes()
        );
    }

    /**
     * Method called to execute each controller retourned by the router and call the next controller defined in the
     * router's result.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param ResultInterface        $routerResult
     * @param ManagerInterface       $manager
     */
    private function doExecute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ResultInterface $routerResult,
        ManagerInterface $manager
    ) {
        $controller = $routerResult->getController();

        $bowl = new Bowl($controller, []);
        $workPlan = \array_merge(
            $this->getParameters($request),
            [
                'client' => $client,
                'request' => $request
            ]
        );

        $bowl->execute(
            $manager,
            $workPlan
        );

        $next = $routerResult->getNext();
        if ($next instanceof ResultInterface) {
            $this->doExecute($client, $request, $next, $manager);
        }
    }
}
