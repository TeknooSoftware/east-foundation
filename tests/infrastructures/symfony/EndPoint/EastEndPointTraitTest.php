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

namespace Teknoo\Tests\East\FoundationBundle\EndPoint;

use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Foundation\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use function strlen;

/**
 * Class ControllerTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EastEndPointTraitTest extends TestCase
{
    public function testGenerateUrlMissingRouter(): void
    {
        $this->expectException(LogicException::class);
        $this->assertEquals(
            '/foo/bar',
            (new class () implements EndPointInterface {
                use EastEndPointTrait;

                public function getUrl(): string
                {
                    return $this->generateUrl('routeName', ['foo' => 'bar']);
                }
            })->getUrl()
        );
    }

    public function testGenerateUrl(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->atLeastOnce())
            ->method('generate')
            ->with('routeName', ['foo' => 'bar'])
            ->willReturn('/foo/bar');

        $this->assertEquals(
            '/foo/bar',
            (new class () {
                use EastEndPointTrait;

                public function getUrl(): string
                {
                    return $this->generateUrl('routeName', ['foo' => 'bar']);
                }
            })->setRouter($router)->getUrl()
        );
    }

    public function testRedirect(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn ($instance) => $instance instanceof ResponseInterface))
            ->willReturnSelf();

        $controller = (new class () implements EndPointInterface {
            use EastEndPointTrait;
            public function getRedirect(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RedirectingInterface
            {
                return $this->redirect($client, 'routeName');
            }
        });

        $this->assertInstanceOf(
            $controller::class,
            $controller->setResponseFactory($responseFactory)->getRedirect($client)
        );
    }

    public function testRedirectToRoute(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->atLeastOnce())
            ->method('generate')
            ->with('routeName', ['foo' => 'bar'])
            ->willReturn('/foo/bar');

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn ($instance): bool => $instance instanceof ResponseInterface))
            ->willReturnSelf();

        $controller = (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRedirect(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RedirectingInterface
            {
                return $this->redirectToRoute($client, 'routeName', ['foo' => 'bar']);
            }
        });

        $this->assertInstanceOf(
            $controller::class,
            $controller->setResponseFactory($responseFactory)->setRouter($router)->getRedirect($client)
        );
    }

    public function testRenderingWithBasicStream(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->method('createResponse')->willReturn($response);

        $stream = $this->createStub(StreamInterface::class);
        $body = null;
        $stream->method('write')->willReturnCallback(
            function ($value) use (&$body) {
                $body = $value;
                return strlen((string) $body);
            }
        );
        $stream->method('getContents')->willReturnCallback(
            function () use (&$body) {
                return $body;
            }
        );
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn ($instance) => $instance instanceof ResponseInterface && $instance->getBody()->getContents()))
            ->willReturnSelf();

        $result = $this->createStub(ResultInterface::class);
        $result->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result) {
                $promise->success($result);
                return $templateEngine;
            }
        );

        $controller = (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RenderingInterface
            {
                return $this->render($client, 'routeName');
            }
        });

        $this->assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderingWithCallbackStream(): void
    {
        $stream = $this->createStub(CallbackStreamInterface::class);
        $callBack = null;
        $stream->method('bind')->willReturnCallback(
            function ($value) use (&$callBack, $stream) {
                $callBack = $value;

                return $stream;
            }
        );
        $stream->method('getContents')->willReturnCallback(
            function () use (&$callBack) {
                return $callBack();
            }
        );
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturn($stream);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn ($instance) => $instance instanceof ResponseInterface && $instance->getBody()->getContents()))
            ->willReturnSelf();

        $result = $this->createStub(ResultInterface::class);
        $result->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result) {
                $promise->success($result);
                return $templateEngine;
            }
        );

        $controller = (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RenderingInterface
            {
                return $this->render($client, 'routeName');
            }
        });

        $this->assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderingWithErrorInRendering(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        $stream = $this->createStub(StreamInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('errorInRequest');

        $result = $this->createStub(ResultInterface::class);
        $result->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result): \PHPUnit\Framework\MockObject\MockObject {
                $promise->fail(new RuntimeException());
                return $templateEngine;
            }
        );

        $controller = (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RenderingInterface
            {
                return $this->render($client, 'routeName');
            }
        });

        $this->assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderNoRendering(): void
    {
        $stream = $this->createStub(StreamInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturn($stream);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->method('withBody')->willReturnCallback(
            function (\Psr\Http\Message\StreamInterface $value) use (&$inStream, $response): static {
                $inStream = $value;
                return $response;
            }
        );
        $response->method('getBody')->willReturnCallback(
            function () use (&$inStream): \Psr\Http\Message\StreamInterface {
                return $inStream;
            }
        );
        $responseFactory->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');
        $client->expects($this->once())
            ->method('errorInRequest')
            ->with(self::callback(fn ($e): bool => $e instanceof RuntimeException));

        (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RenderingInterface
            {
                return $this->render($client, 'routeName');
            }
        }
        )->setStreamFactory($streamFactory)->setResponseFactory($responseFactory)->getRender($client);
    }

    public function testRenderNoRenderingWithCallBackStream(): void
    {
        $stream = $this->createStub(CallbackStreamInterface::class);
        $callBack = null;
        $stream->method('bind')->willReturnCallback(
            function (callable $value) use (&$callBack, $stream): \Teknoo\East\Foundation\Http\Message\CallbackStreamInterface {
                $callBack = $value;

                return $stream;
            }
        );
        $stream->method('getContents')->willReturnCallback(
            function () use (&$callBack): string {
                return $callBack();
            }
        );
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturn($stream);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->method('withBody')->willReturnCallback(
            function (\Psr\Http\Message\StreamInterface $value) use (&$inStream, $response): static {
                $inStream = $value;
                return $response;
            }
        );
        $response->method('getBody')->willReturnCallback(
            function () use (&$inStream): \Psr\Http\Message\StreamInterface {
                return $inStream;
            }
        );
        $responseFactory->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with(self::callback(fn ($e): bool => $e instanceof RuntimeException));

        (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client): \Teknoo\East\Foundation\EndPoint\RenderingInterface
            {
                return $this->render($client, 'routeName');
            }
        }
        )->setStreamFactory($streamFactory)->setResponseFactory($responseFactory)->getRender($client);
    }

    public function testCreateNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateNotFoundException(): never
            {
                throw $this->createNotFoundException();
            }
        }
        )->setResponseFactory($responseFactory)->getCreateNotFoundException();
    }

    public function testCreateAccessDeniedException(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        $this->expectException(AccessDeniedHttpException::class);
        (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateAccessDeniedException(): never
            {
                throw $this->createAccessDeniedException();
            }
        }
        )->setResponseFactory($responseFactory)->getCreateAccessDeniedException();
    }

    public function testGetUserNoStorage(): void
    {
        $this->expectException(LogicException::class);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $responseFactory->method('createResponse')->willReturn($response);

        (new class () implements EndPointInterface {
            use EastEndPointTrait;

            public function getGetUser(): mixed
            {
                return $this->getUser();
            }
        }
        )->setResponseFactory($responseFactory)->getGetUser();
    }

    public function testGetUser(): void
    {
        $storage = $this->createStub(TokenStorageInterface::class, [], [], '', false);

        $this->assertEmpty(
            (new class () implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser(): mixed
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }

    public function testGetUserBadToken(): void
    {
        $storage = $this->createStub(TokenStorageInterface::class, [], [], '', false);
        $storage
            ->method('getToken')
            ->willReturn($this->createStub(TokenInterface::class));

        $this->assertEmpty(
            (new class () implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser(): mixed
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }

    public function testGetUserUser(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturnCallback(fn (): ?\Symfony\Component\Security\Core\User\UserInterface => new class () implements UserInterface {
                public function getPassword(): void
                {
                }

                public function getSalt(): void
                {
                }

                public function getUsername(): void
                {
                }

                public function getRoles(): array
                {
                }

                public function eraseCredentials(): void
                {
                }

                public function getUserIdentifier(): string
                {
                }
            });

        $storage = $this->createStub(TokenStorageInterface::class, [], [], '', false);
        $storage
            ->method('getToken')
            ->willReturn($token);

        $this->assertInstanceOf(
            UserInterface::class,
            (new class () implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser(): mixed
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }
}
