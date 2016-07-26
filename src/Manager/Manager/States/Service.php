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

namespace Teknoo\East\Framework\Manager\Manager\States;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Teknoo\East\Framework\Router\RouterInterface;
use Teknoo\States\State\AbstractState;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Service extends AbstractState
{
    /**
     * Method to call to process a request in East Framework by East's controller
     *
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @return ManagerInterface
     */
    private function running(ClientInterface $client, ServerRequestInterface $request): ManagerInterface
    {
        //Clone this manager, it is immutable and switch it's state
        $manager = clone $this;
        $manager->switchState('Running');
        $manager->dispatchRequest($client, $request);

        return $this;
    }

    /**
     * Method to register router in the manager to process request
     *
     * @param RouterInterface $router
     * @return ManagerInterface
     */
    private function doRegisterRouter(RouterInterface $router): ManagerInterface
    {
        $this->routersList[spl_object_hash($router)] = $router;

        return $this;
    }

    /**
     * Method to unregister router in the manager to process request
     *
     * @param RouterInterface $router
     * @return ManagerInterface
     */
    private function doUnregisterRouter(RouterInterface $router): ManagerInterface
    {
        $routerHash = spl_object_hash($router);
        if (isset($this->routersList[$routerHash])) {
            unset($this->routersList[$routerHash]);
        }

        return $this;
    }
}
