<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Teknoo\East\Foundation\Http\ClientInterface;
use Throwable;

/**
 * Default implementation of Teknoo\East\Foundation\Http\ClientInterface and
 * Teknoo\East\FoundationBundle\Http\ClientWithResponseEventInterface to create client integrated with Symfony and able
 * to manage RequestEvent instance from Symfony Kernel loop.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Client implements ClientWithResponseEventInterface
{
    private ?MessageInterface $response = null;

    private bool $inSilentlyMode = false;

    public function __construct(
        private HttpFoundationFactory $factory,
        private ?RequestEvent $requestEvent = null,
        private ?LoggerInterface $logger = null,
    ) {
        if ($requestEvent instanceof RequestEvent) {
            $this->setRequestEvent($requestEvent);
        }
    }

    public function setRequestEvent(RequestEvent $requestEvent): ClientWithResponseEventInterface
    {
        $this->requestEvent = $requestEvent;

        return $this;
    }

    public function updateResponse(callable $modifier): ClientInterface
    {
        $modifier($this, $this->response);

        return $this;
    }

    public function acceptResponse(MessageInterface $response): ClientInterface
    {
        $this->response = $response;

        return $this;
    }

    public function sendResponse(MessageInterface $response = null, bool $silently = false): ClientInterface
    {
        $silently = $silently || $this->inSilentlyMode;

        if ($response instanceof MessageInterface) {
            $this->acceptResponse($response);
        }

        if (true === $silently && !$this->response instanceof MessageInterface) {
            return $this;
        }

        if (!$this->requestEvent instanceof RequestEvent) {
            throw new RuntimeException('Error, the requestEvent has not been set into the client');
        }

        if (!$this->response instanceof ResponseInterface) {
            throw new RuntimeException('Error, any response object has been pushed to the client');
        }

        $this->requestEvent->setResponse(
            $this->factory->createResponse($this->response)
        );

        $this->response = null;

        return $this;
    }

    public function errorInRequest(Throwable $throwable, bool $silently = false): ClientInterface
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($throwable->getMessage(), ['exception' => $throwable]);
        }

        if (false === $silently) {
            throw $throwable;
        }

        return $this;
    }

    /**
     * To deep clone all elements of the client.
     */
    public function __clone()
    {
        if (null !== $this->factory) {
            $this->factory = clone $this->factory;
        }

        if (null !== $this->requestEvent) {
            $this->requestEvent = clone $this->requestEvent;
        }
    }

    public function mustSendAResponse(): ClientInterface
    {
        $this->inSilentlyMode = false;

        return $this;
    }

    public function sendAResponseIsOptional(): ClientInterface
    {
        $this->inSilentlyMode = true;

        return $this;
    }
}
