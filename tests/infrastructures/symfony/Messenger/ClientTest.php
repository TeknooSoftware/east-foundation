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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Messenger;

use Exception;
use TypeError;
use stdClass;
use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\FoundationBundle\Messenger\Client;

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
    private ?MessageBusInterface $messageBusInterface = null;

    private function getMessageBusInterfaceMock(): MessageBusInterface&MockObject
    {
        if (!$this->messageBusInterface instanceof MessageBusInterface) {
            $this->messageBusInterface = $this->createMock(MessageBusInterface::class);
        }

        return $this->messageBusInterface;
    }

    private function buildClient(?LoggerInterface $logger = null): Client
    {
        return new Client(
            $this->getMessageBusInterfaceMock(),
            $logger
        );
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
            $client->updateResponse(function (ClientInterface $client, ?ResponseInterface $response = null): void {
                $this->assertNotInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
            })
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

    public function testUpdateResponseWithEastResponse(): void
    {
        $response = $this->createMock(EastResponse::class);

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?EastResponse $responsePassed = null) use ($response): void {
                    $this->assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testUpdateResponseWithJsonResponse(): void
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

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?EastResponse $responsePassed = null) use ($response): void {
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

    public function testAcceptPSRResponse(): void
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

    public function testAcceptEastResponse(): void
    {
        $response = $this->createMock(EastResponse::class);

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testAcceptJsonResponse(): void
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

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testSendJsonResponse(): void
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

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPSRResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendEastResponse(): void
    {
        $response = $this->createMock(EastResponse::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendResponseWithoutBus(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->assertInstanceOf(
            $this->getClientClass(),
            new Client(null)->sendResponse($response)
        );
    }

    public function testSendResponseWithAccept(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendResponseAfterReset(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->never())
            ->method('dispatch');

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );

        $client->reset();
        $this->expectException(RuntimeException::class);
        $client->sendResponse();
    }

    public function testSendResponseWithoutResponse(): void
    {
        $this->expectException(RuntimeException::class);
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

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

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

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

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

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $client = $this->buildClient();
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

    public function testErrorInRequest(): void
    {
        $this->expectException(Exception::class);

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilently(): void
    {
        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $this->expectException(Exception::class);

        $client = $this->buildClient($logger);
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilentlyWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $client = $this->buildClient($logger);
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClient()->errorInRequest(new stdClass());
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
