<?php

namespace Teknoo\East\Framework\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ClientInterface
 * @package AppBundle\Http
 */
interface ClientInterface
{
    /**
     * @param ResponseInterface $response
     * @return ClientInterface
     */
    public function successfulResponseFromController(ResponseInterface $response): ClientInterface;

    /**
     * @param \Throwable $throwable
     * @return ClientInterface
     */
    public function errorInRequest(\Throwable $throwable): ClientInterface;
}