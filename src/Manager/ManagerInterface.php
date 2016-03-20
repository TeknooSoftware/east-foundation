<?php

namespace Teknoo\East\Framework\Manager;

use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ManagerInterface
 * @package AppBundle\Http
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