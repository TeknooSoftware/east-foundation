<?php

/*
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

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Http\ClientInterface;

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

    private ?ResponseInterface $response = null;

    public int $returnCode = 0;

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
            throw new \RuntimeException('Error, the output has not been set into the client');
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

    public function acceptResponse(ResponseInterface $response): ClientInterface
    {
        $this->response = $response;

        return $this;
    }

    public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
    {
        if ($response instanceof ResponseInterface) {
            $this->acceptResponse($response);
        }

        if (true === $silently && !$this->response instanceof ResponseInterface) {
            return $this;
        }

        if (!$this->output instanceof OutputInterface) {
            throw new \RuntimeException('Error, the output has not been set into the client');
        }

        if ($this->response instanceof ResponseInterface) {
            $this->output->writeln((string) $this->response->getBody());
        }

        $this->response = null;

        return $this;
    }

    public function errorInRequest(\Throwable $throwable): ClientInterface
    {
        $this->getErrorOutput()->writeln($throwable->getMessage());

        $this->returnCode = $throwable->getCode();

        return $this;
    }
}
