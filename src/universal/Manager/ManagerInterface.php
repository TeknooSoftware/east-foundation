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

namespace Teknoo\East\Foundation\Manager;

use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ManagerInterface is a contract to create manager to process requests in East Foundation. The manager
 * passes the request to each router as the spread has not been stopped.
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
interface ManagerInterface
{
    /**
     * Method to call to process a request in East Foundation by East's controller.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     *
     * @return ManagerInterface
     */
    public function receiveRequestFromClient(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface;

    /**
     * Method to register router in the manager to process request.
     *
     * @param RouterInterface $router
     *
     * @return ManagerInterface
     */
    public function registerRouter(RouterInterface $router): ManagerInterface;

    /**
     * Method to unregister router in the manager to process request.
     *
     * @param RouterInterface $router
     *
     * @return ManagerInterface
     */
    public function unregisterRouter(RouterInterface $router): ManagerInterface;

    /**
     * Method to stop propagation to other routers when a router has determined the request is handle by one of its
     * controllers.
     *
     * @return ManagerInterface
     */
    public function stopPropagation(): ManagerInterface;
}
