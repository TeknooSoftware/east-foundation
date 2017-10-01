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
use Teknoo\East\Foundation\Router\RouterInterface;
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
 * @property  RouterInterface[] $routersList
 *
 * @method ManagerInterface dispatchRequest(ClientInterface $client, ServerRequestInterface $request)
 */
class Service implements StateInterface
{
    use StateTrait;

    /**
     * Builder to call to process a request in East Foundation by East's controller.
     *
     * @return \Closure
     */
    private function running()
    {
        /**
         * Method to call to process a request in East Foundation by East's controller.
         *
         * @param ClientInterface        $client
         * @param ServerRequestInterface $request
         *
         * @return ManagerInterface
         */
        return function (ClientInterface $client, ServerRequestInterface $request): ManagerInterface {
            //Clone this manager, it is immutable and switch it's state
            $manager = clone $this;
            $manager->switchState(Running::class);
            $manager->dispatchRequest($client, $request);

            return $this;
        };
    }

    /**
     * Builder to register router in the manager to process request.
     *
     * @return \Closure
     */
    private function doRegisterRouter()
    {
        /**
         * Method to register router in the manager to process request.
         *
         * @param RouterInterface $router
         * @param int $priority
         *
         * @return ManagerInterface
         */
        return function (RouterInterface $router, int $priority = 10): ManagerInterface {
            $this->routersList[$priority][\spl_object_hash($router)] = $router;

            return $this;
        };
    }

    /**
     * Builder to unregister router in the manager to process request.
     *
     * @return \Closure
     */
    private function doUnregisterRouter()
    {
        /**
         * Method to unregister router in the manager to process request.
         *
         * @param RouterInterface $router
         *
         * @return ManagerInterface
         */

        return function (RouterInterface $router): ManagerInterface {
            $routerHash = spl_object_hash($router);
            foreach ($this->routersList as &$routersList) {
                if (isset($routersList[$routerHash])) {
                    unset($routersList[$routerHash]);
                }
            }

            return $this;
        };
    }
}
