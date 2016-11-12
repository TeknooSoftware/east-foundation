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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
namespace Teknoo\East\FoundationBundle\Http;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Teknoo\East\Foundation\Http\ClientInterface;

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
     * @param GetResponseEvent $getResponseEvent
     * @param HttpFoundationFactory $httpFoundationFactory
     */
    public function __construct(GetResponseEvent $getResponseEvent, HttpFoundationFactory $httpFoundationFactory)
    {
        $this->getResponseEvent = $getResponseEvent;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function responseFromController(ResponseInterface $response): ClientInterface
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