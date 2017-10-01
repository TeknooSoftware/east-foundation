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

namespace Teknoo\East\Foundation\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;

interface MiddlewareInterface
{
    /**
     * Method called by a manager to ask the middleware if it can process the request.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param ManagerInterface       $manager
     *
     * @return MiddlewareInterface
     */
    public function executeRequestFromManager(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface;
}
