<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Http;

use JsonSerializable;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Service\ResetInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\FoundationBundle\Http\Exception\NoRequestException;
use Teknoo\East\FoundationBundle\Http\Exception\NoResponseException;
use Throwable;

use function json_encode;

/**
 * Default implementation of Teknoo\East\Foundation\Client\ClientInterface and
 * Teknoo\East\FoundationBundle\Http\ClientWithResponseEventInterface to create client integrated with Symfony and able
 * to manage RequestEvent instance from Symfony Kernel loop.
 *
 * If the response object does not implement the PSR11 `MessageInterface`, a new standard 200 response with the content
 * of the response will be created and returned. If the response object implements the `\JsonSerializable` interface,
 * the response will be serialized as json instead of string content.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Client implements ClientWithResponseEventInterface, ResetInterface
{
    private EastResponse | MessageInterface | null $response = null;

    private bool $inSilentlyMode = false;

    public function __construct(
        private HttpFoundationFactory $factory,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private ?RequestEvent $requestEvent = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        if ($requestEvent instanceof RequestEvent) {
            $this->setRequestEvent($requestEvent);
        }
    }

    public function reset(): void
    {
        $this->requestEvent = null;
        $this->inSilentlyMode = false;
        $this->response = null;
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

    public function acceptResponse(EastResponse | MessageInterface $response): ClientInterface
    {
        $this->response = $response;

        return $this;
    }

    public function sendResponse(
        EastResponse | MessageInterface | null $response = null,
        bool $silently = false
    ): ClientInterface {
        $silently = $silently || $this->inSilentlyMode;

        if (null !== $response) {
            $this->acceptResponse($response);
        }

        if (true === $silently && null === $this->response) {
            return $this;
        }

        if (!$this->requestEvent instanceof RequestEvent) {
            throw new NoRequestException('Error, the requestEvent has not been set into the client');
        }

        if (
            !$this->response instanceof ResponseInterface
            && $this->response instanceof EastResponse
        ) {
            if ($this->response instanceof JsonSerializable) {
                $content = (string) json_encode($this->response, JSON_THROW_ON_ERROR);
            } else {
                $content = (string) $this->response;
            }

            $originalResponse = $this->response;
            $psrResponse = $this->responseFactory->createResponse();
            $this->response = $psrResponse->withBody(
                $this->streamFactory->createStream($content)
            );

            if ($originalResponse instanceof JsonSerializable) {
                $this->response = $this->response->withAddedHeader('content-type', 'application/json');
            }
        }

        if (!$this->response instanceof ResponseInterface) {
            throw new NoResponseException('Error, any response object has been pushed to the client');
        }

        $this->requestEvent->setResponse(
            $this->factory->createResponse($this->response)
        );

        $this->response = null;

        return $this;
    }

    public function errorInRequest(#[SensitiveParameter] Throwable $throwable, bool $silently = false): ClientInterface
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
        $this->factory = clone $this->factory;

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
