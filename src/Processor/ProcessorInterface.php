<?php

namespace Teknoo\East\Framework\Processor;

use Teknoo\East\Framework\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ProcessorInterface
 * @package Teknoo\East\Framework\Processor
 */
interface ProcessorInterface
{
    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @param array $requestParameters
     * @return ProcessorInterface
     */
    public function executeRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        array $requestParameters
    ): ProcessorInterface;
}