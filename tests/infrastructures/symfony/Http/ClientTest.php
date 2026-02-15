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

namespace Teknoo\Tests\East\FoundationBundle\Http;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use TypeError;
use stdClass;
use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    private ?RequestEvent $requestEvent = null;

    private ?HttpFoundationFactory $httpFoundationFactory = null;

    private ?ResponseFactoryInterface $responseFactory = null;

    private ?StreamFactoryInterface $streamFactory = null;

    private function getRequestEventMock(): RequestEvent&MockObject
    {
        if (!$this->requestEvent instanceof RequestEvent) {
            $this->requestEvent = $this->createMock(RequestEvent::class);
        }

        return $this->requestEvent;
    }

    private function getRequestEventStub(): RequestEvent&Stub
    {
        if (!$this->requestEvent instanceof RequestEvent) {
            $this->requestEvent = $this->createStub(RequestEvent::class);
        }

        return $this->requestEvent;
    }

    private function getHttpFoundationFactoryMock(): HttpFoundationFactory&MockObject
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        }

        return $this->httpFoundationFactory;
    }

    private function getHttpFoundationFactoryStub(): HttpFoundationFactory&Stub
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createStub(HttpFoundationFactory::class);
        }

        return $this->httpFoundationFactory;
    }

    private function getResponseFactoryMock(): ResponseFactoryInterface&Stub
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            $this->responseFactory = $this->createStub(ResponseFactoryInterface::class);

            $response = $this->createStub(ResponseInterface::class);
            $response->method('withBody')->willReturnSelf();
            $response->method('withAddedHeader')->willReturnSelf();

            $this->responseFactory
                ->method('createResponse')
                ->willReturn($response);
        }

        return $this->responseFactory;
    }

    private function getStreamFactoryMock(): StreamFactoryInterface&Stub
    {
        if (!$this->streamFactory instanceof StreamFactoryInterface) {
            $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
        }

        return $this->streamFactory;
    }

    private function buildClient(?LoggerInterface $logger = null): Client
    {
        return new Client(
            $this->getHttpFoundationFactoryMock(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock(),
            $this->getRequestEventMock(),
            $logger
        );
    }

    private function buildClientWithStubs(?LoggerInterface $logger = null): Client
    {
        return new Client(
            $this->getHttpFoundationFactoryStub(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock(),
            $this->getRequestEventStub(),
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
        $this->buildClientWithStubs()->updateResponse(new stdClass());
    }

    public function testUpdateResponse(): void
    {
        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->updateResponse(function (ClientInterface $client, ?ResponseInterface $response = null): void {
                $this->assertNotInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
            })
        );
    }

    public function testUpdateResponseWithPSRResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $client = $this->buildClientWithStubs();
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
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(EastResponse::class);

        $client = $this->buildClientWithStubs();
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

        $client = $this->buildClientWithStubs();
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
        $this->buildClientWithStubs()->acceptResponse(new stdClass());
    }

    public function testAcceptPSRResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testAcceptEastResponse(): void
    {
        $response = $this->createStub(EastResponse::class);

        $client = $this->buildClientWithStubs();
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

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)
        );
    }

    public function testSendPSRResponse(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendEastResponse(): void
    {
        $response = $this->createStub(EastResponse::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPsrAndEastResponse(): void
    {
        $response = new class () implements EastResponse, ResponseInterface, \JsonSerializable {
            public function getProtocolVersion(): string
            {
            }

            public function withProtocolVersion(string $version): MessageInterface
            {
            }

            public function getHeaders(): array
            {
            }

            public function hasHeader(string $name): bool
            {
            }

            public function getHeader(string $name): array
            {
            }

            public function getHeaderLine(string $name): string
            {
            }

            public function withHeader($name, $value): MessageInterface
            {
            }

            public function withAddedHeader($name, $value): MessageInterface
            {
            }

            public function withoutHeader($name): MessageInterface
            {
            }

            public function getBody(): StreamInterface
            {
            }

            public function withBody(StreamInterface $body): MessageInterface
            {
            }

            public function __toString(): string
            {
                throw new \Exception("Must be not called");
            }

            public function jsonSerialize(): mixed
            {
                throw new \Exception("Must be not called");
            }

            public function getStatusCode(): int
            {
            }

            public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
            {
            }

            public function getReasonPhrase(): string
            {
            }
        };

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
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

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendPSRResponseWithAccept(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendEastResponseWithAccept(): void
    {
        $response = $this->createStub(EastResponse::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendJsonResponseWithAccept(): void
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

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse()
        );
    }

    public function testSendResponseAfterReset(): void
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

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
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
        $client = $this->buildClientWithStubs();

        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse()
        );
    }

    public function testSendResponseWithoutRequestEvent(): void
    {
        $this->expectException(RuntimeException::class);
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $client = new Client(
            $this->getHttpFoundationFactoryStub(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );

        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response)
        );
    }

    public function testSendResponseSilently(): void
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
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
        $response = $this->createStub(ResponseInterface::class);

        $this->getRequestEventMock()
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->callback(fn ($response): bool => $response instanceof Response))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects($this->once())
            ->method('createResponse')
            ->with($this->callback(fn ($response): bool => $response instanceof ResponseInterface))
            ->willReturn($this->createStub(Response::class));

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
        $response = $this->createStub(ResponseInterface::class);

        $this->getRequestEventStub()
            ->method('setResponse')
            ->willReturnSelf();

        $this->getHttpFoundationFactoryStub()
            ->method('createResponse')
            ->willReturn($this->createStub(Response::class));

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->acceptResponse($response)->sendResponse(null, true)
        );
    }

    public function testSendResponseWithoutResponseSilently(): void
    {
        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse(null, true)
        );
    }

    public function testSendResponseWithoutRequestEventSilently(): void
    {
        $this->expectException(RuntimeException::class);
        /**
         * @var ResponseInterface
         */
        $response = $this->createStub(ResponseInterface::class);

        $client = new Client(
            $this->getHttpFoundationFactoryStub(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->sendResponse($response, true)
        );
    }

    public function testSendResponseError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClientWithStubs()->sendResponse(new stdClass());
    }

    public function testSendResponseError2(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClientWithStubs()->sendResponse(null, new stdClass());
    }

    public function testErrorInRequest(): void
    {
        $this->expectException(Exception::class);

        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilently(): void
    {
        $client = $this->buildClientWithStubs();
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

        $client = $this->buildClientWithStubs($logger);
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestSilentlyWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $client = $this->buildClientWithStubs($logger);
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'), true)
        );
    }

    public function testErrorInRequestWithoutRequestEvent(): void
    {
        $this->expectException(Exception::class);
        $client = new Client(
            $this->getHttpFoundationFactoryStub(),
            $this->getResponseFactoryMock(),
            $this->getStreamFactoryMock()
        );
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    public function testErrorInRequestError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClientWithStubs()->errorInRequest(new stdClass());
    }

    public function testSetRequestEvent(): void
    {
        $client = $this->buildClientWithStubs();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->setRequestEvent($this->createStub(RequestEvent::class))
        );
    }

    public function testSetRequestEventError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildClientWithStubs()->setRequestEvent(new stdClass());
    }

    public function testClone(): void
    {
        $client = $this->buildClientWithStubs();
        $clonedClient = clone $client;

        $this->assertInstanceOf(Client::class, $clonedClient);

        $reflectionProperty = new \ReflectionProperty($clonedClient, 'factory');
        $this->assertNotSame($this->getHttpFoundationFactoryStub(), $reflectionProperty->getValue($clonedClient));
    }

    public function testMustSendAResponse(): void
    {
        $client = $this->buildClientWithStubs();

        $this->assertInstanceOf(Client::class, $client->mustSendAResponse());
    }

    public function testSendAResponseIsOptional(): void
    {
        $client = $this->buildClientWithStubs();

        $this->assertInstanceOf(Client::class, $client->sendAResponseIsOptional());
    }
}
