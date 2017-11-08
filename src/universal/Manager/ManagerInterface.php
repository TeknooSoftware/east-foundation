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
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Interface ManagerInterface is a contract to create manager to process requests in East Foundation. The manager
 * passes the request to each middleware as the spread has not been stopped.
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
interface ManagerInterface extends ChefInterface
{
    /**
     * Method to call to process a request in East Foundation by East's controller.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     *
     * @return ManagerInterface
     */
    public function receiveRequest(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface;

    /**
     * Method to call from a middleware to continue the propagation of the request through middlewares registered into
     * the queue of the manager.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     *
     * @return ManagerInterface
     */
    public function continueExecution(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface;

    /**
     * Method to stop propagation to other middlewares when a middleware has determined the request
     * is handle by one of its controllers.
     *
     * @return ManagerInterface
     */
    public function stop(): ManagerInterface;
}
