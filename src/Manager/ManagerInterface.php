<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Manager;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Interface ManagerInterface is a contract to create manager to process requests in East Foundation. The manager
 * passes the request to each middleware as the spread has not been stopped.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ManagerInterface extends ChefInterface
{
    /*
     * Method to call to process a request in East Foundation by East's controller.
     */
    public function receiveRequest(
        ClientInterface $client,
        MessageInterface $message
    ): ManagerInterface;

    /*
     * Method to call from a middleware to continue the propagation of the request through middlewares registered into
     * the queue of the manager.
     */
    public function continueExecution(
        ClientInterface $client,
        MessageInterface $message
    ): ManagerInterface;

    /*
     * Method to call from a middleware to update message into the manager's workplan
     */
    public function updateMessage(
        MessageInterface $message
    ): ManagerInterface;

    /*
     * Method to stop propagation to other middlewares when a middleware has determined the request
     * is handle by one of its controllers.
     */
    public function stop(): ManagerInterface;
}
