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
class Running extends AbstractState
{
    /**
     * Build a Generator to stop the list at reception of the stop message
     * @return \Generator
     */
    private function iterateRouter()
    {
        foreach ($this->routersList as $router) {
            //Fetch eatch router
            yield $router;

            //Stop propagation logic is written here to avoid complex instructions in dispatchRequest.
            //The loop in dispatchRequest is agnostic.
            //Stop to fetch a router if the current router has sent a signal to this manager.
            if (false === $this->doRequestPropagation) {
                break;
            }
        }
    }

    /**
     * To dispatch the request to all routers while a message was not receive to stop the propaggation
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @return ManagerInterface
     */
    private function dispatchRequest(ClientInterface $client, ServerRequestInterface $request): ManagerInterface
    {
        $this->doRequestPropagation = true;

        /**
         * @var RouterInterface $router
         */
        foreach ($this->iterateRouter() as $router) {
            $router->receiveRequestFromServer($client, $request, $this);
        }

        $this->switchState('HadRun');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    private function doStopPropagation(): ManagerInterface
    {
        $this->doRequestPropagation = false;

        return $this;
    }
}