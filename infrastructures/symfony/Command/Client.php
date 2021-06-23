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

namespace Teknoo\East\FoundationBundle\Command;

use JsonSerializable;
use Psr\Http\Message\MessageInterface;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface;
use Throwable;

use function json_encode;

/**
 * Default implementation of Teknoo\East\Foundation\Http\ClientInterface to Symfony Command to use East foundation
 * in CLI.
 *
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
    private ?OutputInterface $output = null;

    private ResponseInterface | MessageInterface | null $response = null;

    public int $returnCode = 0;

    private bool $inSilentlyMode = false;

    public function __construct(?OutputInterface $output = null)
    {
        if (null !== $output) {
            $this->setOutput($output);
        }
    }

    public function setOutput(?OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    private function getErrorOutput(): OutputInterface
    {
        if (!$this->output instanceof OutputInterface) {
            throw new RuntimeException('Error, the output has not been set into the client');
        }

        if ($this->output instanceof ConsoleOutputInterface) {
            return $this->output->getErrorOutput();
        }

        return $this->output;
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

        if (!$this->output instanceof OutputInterface) {
            throw new RuntimeException('Error, the output has not been set into the client');
        }

        if ($this->response instanceof MessageInterface) {
            $this->output->writeln((string) $this->response->getBody());
        } elseif ($this->response instanceof JsonSerializable) {
            $this->output->writeln((string) json_encode($this->response));
        } else {
            $this->output->writeln((string) $this->response);
        }

        $this->response = null;

        return $this;
    }

    public function errorInRequest(Throwable $throwable, bool $silently = false): ClientInterface
    {
        $this->getErrorOutput()->writeln($throwable->getMessage());

        $this->returnCode = $throwable->getCode();

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
