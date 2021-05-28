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

namespace Teknoo\East\FoundationBundle\Messenger;

use Psr\Http\Message\MessageInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Throwable;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Client implements ClientInterface
{
    private ?MessageInterface $response = null;

    private bool $inSilentlyMode = false;

    public function __construct(
        private ?MessageBusInterface $bus,
        private ?LoggerInterface $logger = null,
    ) {
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

        if (!$this->response instanceof MessageInterface) {
            throw new RuntimeException('Error, any response object has been pushed to the client');
        }

        if ($this->bus instanceof MessageBusInterface) {
            $this->bus->dispatch(
                new Envelope($this->response)
            );
        }

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
