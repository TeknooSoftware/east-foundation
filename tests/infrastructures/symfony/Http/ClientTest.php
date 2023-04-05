<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\FoundationBundle\Http\Client;

/**
 * Class ClientTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\FoundationBundle\Http\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var RequestEvent
     */
    private $requestEvent;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    private ?ResponseFactoryInterface $responseFactory = null;

    private ?StreamFactoryInterface $streamFactory = null;

    /**
     * @return RequestEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestEventMock(): RequestEvent
    {
        if (!$this->requestEvent instanceof RequestEvent) {
            $this->requestEvent = $this->createMock(RequestEvent::class);
        }

        return $this->requestEvent;
    }

    /**
     * @return HttpFoundationFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getHttpFoundationFactoryMock(): HttpFoundationFactory
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        }

        return $this->httpFoundationFactory;
    }

    /**
     * @return ResponseFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getResponseFactoryMock(): ResponseFactoryInterface
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);

            $response = $this->createMock(ResponseInterface::class);
            $response->expects(self::any())->method('withBody')->willReturnSelf();
            $response->expects(self::any())->method('withAddedHeader')->willReturnSelf();

            $this->responseFactory->expects(self::any())
                ->method('createResponse')
                ->willReturn($response);
        }

        return $this->responseFactory;
    }

    /**
     * @return StreamFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getStreamFactoryMock(): StreamFactoryInterface
    {
        if (!$this->streamFactory instanceof StreamFactoryInterface) {
            $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        }

        return $this->streamFactory;
    }

    /**
     * @return Client
     */
    private function buildClient(LoggerInterface $logger = null): Client
    {
        return new Client(
            $this->getHttpFoundationFactoryMock(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock(),
            $this->getRequestEventMock(),
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
            $client->updateResponse(function (ClientInterface $client, ResponseInterface $response=null) {
                self::assertEmpty($response);
            })
        );
    }

    public function testUpdateResponseWithPSRResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, ResponseInterface $responsePassed=null) use ($response) {
                    self::assertEquals($response, $responsePassed);
                }
            )
        );
    }

    public function testUpdateResponseWithEastResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(EastResponse::class);

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->updateResponse(
                function (ClientInterface $client, EastResponse $responsePassed=null) use ($response) {
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
                function (ClientInterface $client, EastResponse $responsePassed=null) use ($response) {
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
    
    public function testSendPSRResponse()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendEastResponse()
    {
        $response = $this->createMock(EastResponse::class);

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPsrAndEastResponse()
    {
        $response = new class implements EastResponse, ResponseInterface, \JsonSerializable {
            public function getProtocolVersion() {}
            public function withProtocolVersion($version) {}
            public function getHeaders() {}
            public function hasHeader($name) {}
            public function getHeader($name) {}
            public function getHeaderLine($name) {}
            public function withHeader($name, $value) {}
            public function withAddedHeader($name, $value) {}
            public function withoutHeader($name) {}
            public function getBody() {}
            public function withBody(StreamInterface $body) {}
            public function __toString(): string
            {
                throw new \Exception("Must be not called");
            }
            public function jsonSerialize(): mixed
            {
                throw new \Exception("Must be not called");
            }
            public function getStatusCode() {}
            public function withStatus($code, $reasonPhrase = '') {}
            public function getReasonPhrase() {}
        };

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
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

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPSRResponseWithAccept()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendEastResponseWithAccept()
    {
        $response = $this->createMock(EastResponse::class);

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendJsonResponseWithAccept()
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

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
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

    public function testSendResponseWithoutRequestEvent()
    {
        $this->expectException(\RuntimeException::class);
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = new Client(
            $this->getHttpFoundationFactoryMock(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );

        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendResponseSilently()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

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

        $this->getRequestEventMock()
            ->expects(self::once())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::once())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

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

        $this->getRequestEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(fn($response) => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(fn($response) => $response instanceof ResponseInterface))
            ->willReturn($this->createMock(Response::class));

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

    public function testSendResponseWithoutRequestEventSilently()
    {
        $this->expectException(\RuntimeException::class);
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = new Client(
            $this->getHttpFoundationFactoryMock(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)
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
        $logger->expects(self::once())->method('error');

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
        $logger->expects(self::once())->method('error');

        $client = $this->buildClient($logger);
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestWithoutRequestEvent()
    {
        $this->expectException(\Exception::class);
        $client = new Client(
            $this->getHttpFoundationFactoryMock(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->errorInRequest(new \stdClass());
    }

    public function testSetRequestEvent()
    {
        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->setRequestEvent($this->createMock(RequestEvent::class))
        );
    }

    public function testSetRequestEventError()
    {
        $this->expectException(\TypeError::class);
        $this->buildClient()->setRequestEvent(new \stdClass());
    }

    public function testClone()
    {
        $client = $this->buildClient();
        $clonedClient = clone $client;

        self::assertInstanceOf(Client::class, $clonedClient);

        $reflectionProperty = new \ReflectionProperty($clonedClient, 'factory');
        $reflectionProperty->setAccessible(true);
        self::assertNotSame($this->getHttpFoundationFactoryMock(), $reflectionProperty->getValue($clonedClient));
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
