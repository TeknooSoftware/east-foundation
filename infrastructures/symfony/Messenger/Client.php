<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Messenger;

use Psr\Http\Message\MessageInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface;
use Teknoo\East\FoundationBundle\Messenger\Exception\NoResponseException;
use Throwable;

/**
 * Default implementation of Teknoo\East\Foundation\Client\ClientInterface dedicated to Symfony Messenger
 * to use East foundation to represent the bus and emit message on message.
 *
 * Any response (PSR11 `MessageInterface`, East `ResponseInterface` or `\JsonSerializable`) are directly passed to
 * Symfony Messenger bus as object.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Client implements ClientInterface, ResetInterface
{
    private ResponseInterface | MessageInterface | null $response = null;

    private bool $inSilentlyMode = false;

    public function __construct(
        private readonly ?MessageBusInterface $bus,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function reset(): void
    {
        $this->response = null;
        $this->inSilentlyMode = false;
    }

    public function updateResponse(callable $modifier): ClientInterface
    {
        $modifier($this, $this->response);

        return $this;
    }

    public function acceptResponse(ResponseInterface | MessageInterface $response): ClientInterface
    {
        $this->response = $response;

        return $this;
    }

    public function sendResponse(
        ResponseInterface | MessageInterface | null $response = null,
        bool $silently = false
    ): ClientInterface {
        $silently = $silently || $this->inSilentlyMode;

        if (null !== $response) {
            $this->acceptResponse($response);
        }

        if (true === $silently && null === $this->response) {
            return $this;
        }

        if (null === $this->response) {
            throw new NoResponseException('Error, any compliant response object has been pushed to the client');
        }

        if ($this->bus instanceof MessageBusInterface) {
            $this->bus->dispatch(
                new Envelope($this->response)
            );
        }

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
