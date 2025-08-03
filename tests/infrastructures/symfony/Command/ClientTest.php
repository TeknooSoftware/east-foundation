<?php

/**
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
 * @link        Command://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Command;

use TypeError;
use stdClass;
use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\FoundationBundle\Command\Client;

/**
 * Class ClientTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    private ?OutputInterface $output = null;

    private function getOutputMock(): OutputInterface&MockObject
    {
        if (!$this->output instanceof OutputInterface) {
            $this->output = $this->createMock(OutputInterface::class);
        }

        return $this->output;
    }

    private function buildClient(): Client
    {
        return new Client($this->getOutputMock());
    }

    private function getClientClass(): string
    {
        return Client::class;
    }

    public function testUpdateResponseError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->updateResponse(new stdClass());
    }

    public function testUpdateResponse(): void
    {
        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->updateResponse(
                function (
                    ClientInterface $client,
                    ?ResponseInterface $response = null
                ) {
                    $this->assertEmpty($response);
                }
            )
        );
    }

    public function testUpdateResponseWithResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?ResponseInterface $responsePassed = null) use ($response): void {
                    $this->assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testAcceptResponseError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->acceptResponse(new stdClass());
    }

    public function testAcceptResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testSendResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);


        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendResponseWithAccept(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendJsonWithAccept(): void
    {
        $response = new class () implements EastResponse, \JsonSerializable {
            public function __toString(): string
            {
                return 'foo';
            }

            public function jsonSerialize(): mixed
            {
                return ['foo' => 'bar'];
            }
        };

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendResponsetWithAccept(): void
    {
        $response = $this->createMock(EastResponse::class);

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendResponseWithoutResponse(): void
    {
        $client = $this->buildClient();

        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse()
        );
    }

    public function testSendResponseSilently(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)
        );
    }

    public function testSendResponseCleanResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getOutputMock()
            ->expects($this->once())
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)->sendResponse(null, true)
        );
    }

    public function testSendResponseCleanEastResponse(): void
    {
        $response = $this->createMock(EastResponse::class);

        $this->getOutputMock()
            ->expects($this->once())
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)->sendResponse(null, true)
        );
    }

    public function testSendResponseCleanJson(): void
    {
        $response = new class () implements EastResponse, \JsonSerializable {
            public function __toString(): string
            {
                return 'foo';
            }

            public function jsonSerialize(): mixed
            {
                return ['foo' => 'bar'];
            }
        };

        $this->getOutputMock()
            ->expects($this->once())
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)->sendResponse(null, true)
        );
    }

    public function testSendResponseWithAcceptSilently(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse(null, true)
        );
    }

    public function testSendResponseAfterReset(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );

        $client->reset();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse(null, true)
        );
    }

    public function testSendResponseWithoutOutput(): void
    {
        $this->expectException(RuntimeException::class);

        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = new Client();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse(null, true)
        );
    }

    public function testSendResponseWithoutResponseSilently(): void
    {
        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse(null, true)
        );
    }

    public function testSendResponseError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->sendResponse(new stdClass());
    }

    public function testSendResponseError2(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->sendResponse(null, new stdClass());
    }

    public function testErrorInRequestWithoutOutput(): void
    {
        $this->expectException(RuntimeException::class);

        $client = new Client();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestWithStandardOutput(): void
    {
        $this->getOutputMock()
            ->method('writeln');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestWithConsoleOutput(): void
    {
        $this->getOutputMock()
            ->method('writeln');

        $output = $this->createMock(ConsoleOutputInterface::class);
        $output
            ->method('getErrorOutput')
            ->willReturn($this->getOutputMock());

        $client = new Client($output);
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->errorInRequest(new stdClass());
    }

    public function testClone(): void
    {
        $client = $this->buildClient();
        $clonedClient = clone $client;

        $this->assertInstanceOf(Client::class, $clonedClient);
    }

    public function testSetOutput(): void
    {
        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->setOutput($this->createMock(OutputInterface::class))
        );
    }

    public function testSetOutputError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->setOutput(new stdClass());
    }

    public function testMustSendAResponse(): void
    {
        $client = $this->buildClient();

        $this->assertInstanceOf(Client::class, $client->mustSendAResponse());
    }

    public function testSendAResponseIsOptional(): void
    {
        $client = $this->buildClient();

        $this->assertInstanceOf(Client::class, $client->sendAResponseIsOptional());
    }
}
