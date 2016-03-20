<?php

namespace Teknoo\East\Framework\Http;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class Client
 * @package AppBundle\Http
 */
class Client implements ClientInterface
{
    /**
     * @var GetResponseEvent
     */
    private $getResponseEvent;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * Client constructor.
     * @param GetResponseEvent $event
     * @param HttpFoundationFactory $factory
     */
    public function __construct(GetResponseEvent $event, HttpFoundationFactory $factory)
    {
        $this->getResponseEvent = $event;
        $this->httpFoundationFactory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function successfulResponseFromController(ResponseInterface $response): ClientInterface
    {
        $this->getResponseEvent->setResponse(
            $this->httpFoundationFactory->createResponse($response)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function errorInRequest(\Throwable $throwable): ClientInterface
    {
        $this->getResponseEvent->setResponse(
            new Response(
                $throwable->getMessage(),
                500
            )
        );

        return $this;
    }
}