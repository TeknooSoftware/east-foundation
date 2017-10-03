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
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
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
 *
 * @property QueueInterface $queue
 */
class Running implements StateInterface
{
    use StateTrait;

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
            /**
             * @var MiddlewareInterface $middleware
             */
            foreach ($this->queue->iterate() as $middleware) {
                $middleware->execute($client, $request, $this);
            }

            return $this;
        };
    }

    /**
     * Builder to stop propagation to other middlewares when a middleware has determined
     * the request is handle by one of its controllers.
     *
     * @return \Closure
     */
    private function doStop()
    {
        /**
         * Method to stop propagation to other middlewares when a middleware
         * has determined the request is handle by one of its controllers.
         *
         * @return ManagerInterface
         */
        return function (): ManagerInterface {
            $this->queue->stop();

            return $this;
        };
    }
}
