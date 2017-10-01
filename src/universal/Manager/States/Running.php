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

namespace Teknoo\East\Foundation\Manager\States;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @mixin Manager
 *
 * @property  MiddlewareInterface[] $middlewaresList
 * @property bool $doRequestPropagation
 */
class Running implements StateInterface
{
    use StateTrait;

    /**
     * Builder to build a Generator to stop the list at reception of the stop message.
     *
     * @return \Closure
     */
    private function iterate()
    {
        /**
         * Build a Generator to stop the list at reception of the stop message.
         *
         * @return \Generator
         */
        return function () {
            $listPrioritized = $this->middlewaresList;
            ksort($listPrioritized);
            foreach ($listPrioritized as &$middlewaresList) {
                foreach ($middlewaresList as $middleware) {
                    //Fetch each middleware
                    yield $middleware;

                    //Stop propagation logic is written here to avoid complex instructions in dispatchRequest.
                    //The loop in dispatchRequest is agnostic.
                    //Stop to fetch a middleware if the current middleware has sent a signal to this manager.
                    if (false === $this->doRequestPropagation) {
                        break;
                    }
                }
            }
        };
    }

    /**
     * Builder to dispatch the request to all middlewares while a message was not receive to stop the propaggation.
     *
     * @return \Closure
     */
    private function dispatchRequest()
    {
        /**
         * To dispatch the request to all middlewares while a message was not receive to stop the propaggation.
         *
         * @param ClientInterface        $client
         * @param ServerRequestInterface $request
         *
         * @return ManagerInterface
         */
        return function (ClientInterface $client, ServerRequestInterface $request): ManagerInterface {
            $this->doRequestPropagation = true;

            /**
             * @var MiddlewareInterface $middleware
             */
            foreach ($this->iterate() as $middleware) {
                $middleware->receiveRequestFromServer($client, $request, $this);
            }

            $this->switchState(HadRun::class);

            return $this;
        };
    }

    /**
     * Builder to stop propagation to other middlewares when a middleware has determined the request is handle by one of its
     * controllers.
     *
     * @return \Closure
     */
    private function doStopPropagation()
    {
        /**
         * Method to stop propagation to other middlewares when a middleware has determined the request is handle by one of its
         * controllers.
         *
         * @return ManagerInterface
         */
        return function (): ManagerInterface {
            $this->doRequestPropagation = false;

            return $this;
        };
    }
}
