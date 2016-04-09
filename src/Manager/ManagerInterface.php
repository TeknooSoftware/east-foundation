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

namespace Teknoo\East\Framework\Manager;

use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ManagerInterface
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ManagerInterface
{
    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @return ManagerInterface
     */
    public function receiveRequestFromClient(ClientInterface $client, ServerRequestInterface $request): ManagerInterface;

    /**
     * @param RouterInterface $router
     * @return ManagerInterface
     */
    public function registerRouter(RouterInterface $router): ManagerInterface;

    /**
     * @param RouterInterface $router
     * @return ManagerInterface
     */
    public function unregisterRouter(RouterInterface $router): ManagerInterface;

    /**
     * @return ManagerInterface
     */
    public function stopPropagation(): ManagerInterface;
}