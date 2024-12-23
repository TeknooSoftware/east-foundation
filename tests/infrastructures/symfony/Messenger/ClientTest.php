<?php
/**
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

namespace Teknoo\Tests\East\FoundationBundle\Messenger;

use PHPUnit\Framework\Attributes\CoversClass;
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    /**
     * @var MessageBusInterface
     */
    private $messageBusInterface;

    /**
     * @return MessageBusInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMessageBusInterfaceMock(): MessageBusInterface
    {
        if (!$this->messageBusInterface instanceof MessageBusInterface) {
            $this->messageBusInterface = $this->createMock(MessageBusInterface::class);
        }

        return $this->messageBusInterface;
    }

    /**
     * @return Client
     */
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

    public function testUpdateResponseError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->updateResponse(new \stdClass());
    }
    
    public function testUpdateResponse()
    {
        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->updateResponse(function (ClientInterface $client, ?ResponseInterface $response=null) {
                self::assertEmpty($response);
            })
        );
    }

    public function testUpdateResponseWithResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?ResponseInterface $responsePassed=null) use ($response) {
                    self::assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testUpdateResponseWithEastResponse()
    {
        $response = $this->createMock(EastResponse::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?EastResponse $responsePassed=null) use ($response) {
                    self::assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testUpdateResponseWithJsonResponse()
    {

        $response = new class implements EastResponse, \JsonSerializable
        {
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
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ?EastResponse $responsePassed=null) use ($response) {
                    self::assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testAcceptResponseError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->acceptResponse(new \stdClass());
    }
    
    public function testAcceptPSRResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testAcceptEastResponse()
    {
        $response = $this->createMock(EastResponse::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testAcceptJsonResponse()
    {
        $response = new class implements EastResponse, \JsonSerializable
        {
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
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }
    
    public function testSendJsonResponse()
    {

        $response = new class implements EastResponse, \JsonSerializable
        {
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
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPSRResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendEastResponse()
    {
        $response = $this->createMock(EastResponse::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendResponseWithoutBus()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        self::assertInstanceOf(
            $this->getClientClass(),
            (new Client(null))->sendResponse($response)
        );
    }

    public function testSendResponseWithAccept()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendResponseAfterReset()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->never())
            ->method('dispatch');

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );

        $client->reset();
        $this->expectException(\RuntimeException::class);
        $client->sendResponse();
    }

    public function testSendResponseWithoutResponse()
    {
        $this->expectException(\RuntimeException::class);
        $client = $this->buildClient();

        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse()
        );
    }

    public function testSendResponseSilently()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)
        );
    }

    public function testSendResponseCleanResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)->sendResponse(null, true)
        );
    }

    public function testSendResponseWithAcceptSilently()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getMessageBusInterfaceMock()
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse(null, true)
        );
    }

    public function testSendResponseWithoutResponseSilently()
    {
        $client = $this->buildClient();

        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse(null, true)
        );
    }

    public function testSendResponseError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->sendResponse(new \stdClass());
    }

    public function testSendResponseError2()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->sendResponse(null, new \stdClass());
    }

    public function testErrorInRequest()
    {
        $this->expectException(\Exception::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilently()
    {
        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestWithLogger()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $this->expectException(\Exception::class);

        $client = $this->buildClient($logger);
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilentlyWithLogger()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $client = $this->buildClient($logger);
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->errorInRequest(new \stdClass());
    }

    public function testMustSendAResponse()
    {
        $client = $this->buildClient();

        self::assertInstanceOf(Client::class, $client->mustSendAResponse());
    }

    public function testSendAResponseIsOptional()
    {
        $client = $this->buildClient();

        self::assertInstanceOf(Client::class, $client->sendAResponseIsOptional());
    }
}
