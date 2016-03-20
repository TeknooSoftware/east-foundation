<?php

namespace Teknoo\East\Framework\Router;

use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouterInterface
 * @package AppBundle\Router
 */
interface RouterInterface
{
    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @param ManagerInterface $manager
     * @return RouterInterface
     */
    public function receiveRequestFromServer(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): RouterInterface;
}