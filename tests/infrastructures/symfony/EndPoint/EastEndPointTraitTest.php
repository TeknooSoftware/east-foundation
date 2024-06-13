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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\EndPoint;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
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
use Teknoo\East\FoundationBundle\EndPoint\AuthenticationTrait;
use Teknoo\East\FoundationBundle\EndPoint\ExceptionTrait;
use Teknoo\East\FoundationBundle\EndPoint\ResponseFactoryTrait;
use Teknoo\East\FoundationBundle\EndPoint\RoutingTrait;
use Teknoo\East\FoundationBundle\EndPoint\TemplatingTrait;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(TemplatingTrait::class)]
#[CoversTrait(RoutingTrait::class)]
#[CoversTrait(ResponseFactoryTrait::class)]
#[CoversTrait(ExceptionTrait::class)]
#[CoversTrait(\Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait::class)]
#[CoversTrait(AuthenticationTrait::class)]
class EastEndPointTraitTest extends TestCase
{
    public function testGenerateUrlMissingRouter()
    {
        $this->expectException(LogicException::class);
        self::assertEquals(
            '/foo/bar',
            (new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getUrl()
                {
                    return $this->generateUrl('routeName', ['foo' => 'bar']);
                }
            })->getUrl()
        );
    }

    public function testGenerateUrl()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->any())
            ->method('generate')
            ->with('routeName', ['foo' => 'bar'])
            ->willReturn('/foo/bar');

        self::assertEquals(
            '/foo/bar',
            (new class() {
                use EastEndPointTrait;

                public function getUrl()
                {
                    return $this->generateUrl('routeName', ['foo' => 'bar']);
                }
            })->setRouter($router)->getUrl()
        );
    }

    public function testRedirect()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn($instance) => $instance instanceof ResponseInterface))
            ->willReturnSelf();

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;
            public function getRedirect(ClientInterface $client)
            {
                return $this->redirect($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            $controller::class,
            $controller->setResponseFactory($responseFactory)->getRedirect($client)
        );
    }

    public function testRedirectToRoute()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->any())
            ->method('generate')
            ->with('routeName', ['foo' => 'bar'])
            ->willReturn('/foo/bar');

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn($instance) => $instance instanceof ResponseInterface))
            ->willReturnSelf();

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRedirect(ClientInterface $client)
            {
                return $this->redirectToRoute($client, 'routeName', ['foo' => 'bar']);
            }
        });

        self::assertInstanceOf(
            $controller::class,
            $controller->setResponseFactory($responseFactory)->setRouter($router)->getRedirect($client)
        );
    }

    public function testRenderingWithBasicStream()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects($this->any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects($this->any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $body = null;
        $stream->expects($this->any())->method('write')->willReturnCallback(
            function ($value) use (&$body) {
                $body = $value;
                return strlen((string) $body);
            }
        );
        $stream->expects($this->any())->method('getContents')->willReturnCallback(
            function () use (&$body) {
                return $body;
            }
        );
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->any())->method('createStream')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn($instance) => $instance instanceof ResponseInterface && $instance->getBody()->getContents()))
            ->willReturnSelf();

        $result = $this->createMock(ResultInterface::class);
        $result->expects($this->any())->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result) {
                $promise->success($result);
                return $templateEngine;
            }
        );

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderingWithCallbackStream()
    {
        $stream = $this->createMock(CallbackStreamInterface::class);
        $callBack = null;
        $stream->expects($this->any())->method('bind')->willReturnCallback(
            function ($value) use (&$callBack, $stream) {
                $callBack = $value;

                return $stream;
            }
        );
        $stream->expects($this->any())->method('getContents')->willReturnCallback(
            function () use (&$callBack) {
                return $callBack();
            }
        );
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->any())->method('createStream')->willReturn($stream);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects($this->any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects($this->any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn($instance) => $instance instanceof ResponseInterface && $instance->getBody()->getContents()))
            ->willReturnSelf();

        $result = $this->createMock(ResultInterface::class);
        $result->expects($this->any())->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result) {
                $promise->success($result);
                return $templateEngine;
            }
        );

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderingWithErrorInRendering()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->any())->method('createStream')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('errorInRequest');

        $result = $this->createMock(ResultInterface::class);
        $result->expects($this->any())->method('__toString')->willReturn('fooBar');

        $templateEngine = $this->createMock(EngineInterface::class);
        $templateEngine->expects($this->once())->method('render')->willReturnCallback(
            function (PromiseInterface $promise) use ($templateEngine, $result) {
                $promise->fail(new RuntimeException());
                return $templateEngine;
            }
        );

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            $controller::class,
            $controller
                ->setStreamFactory($streamFactory)
                ->setResponseFactory($responseFactory)
                ->setTemplating($templateEngine)
                ->getRender($client)
        );
    }

    public function testRenderNoRendering()
    {
        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->any())->method('createStream')->willReturn($stream);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects($this->any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects($this->any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');
        $client->expects($this->once())
            ->method('errorInRequest')
            ->with(self::callback(fn($e) => $e instanceof RuntimeException));

        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        }
        )->setStreamFactory($streamFactory)->setResponseFactory($responseFactory)->getRender($client);
    }

    public function testRenderNoRenderingWithCallBackStream()
    {
        $stream = $this->createMock(CallbackStreamInterface::class);
        $callBack = null;
        $stream->expects($this->any())->method('bind')->willReturnCallback(
            function ($value) use (&$callBack, $stream) {
                $callBack = $value;

                return $stream;
            }
        );
        $stream->expects($this->any())->method('getContents')->willReturnCallback(
            function () use (&$callBack) {
                return $callBack();
            }
        );
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->any())->method('createStream')->willReturn($stream);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects($this->any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects($this->any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $client = $this->createMock(ClientInterface::class);

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with(self::callback(fn($e) => $e instanceof RuntimeException));

        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        }
        )->setStreamFactory($streamFactory)->setResponseFactory($responseFactory)->getRender($client);
    }

    public function testCreateNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateNotFoundException(): never
            {
                throw $this->createNotFoundException();
            }
        }
        )->setResponseFactory($responseFactory)->getCreateNotFoundException();
    }

    public function testCreateAccessDeniedException()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        $this->expectException(AccessDeniedHttpException::class);
        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateAccessDeniedException(): never
            {
                throw $this->createAccessDeniedException();
            }
        }
        )->setResponseFactory($responseFactory)->getCreateAccessDeniedException();
    }

    public function testGetUserNoStorage()
    {
        $this->expectException(LogicException::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $responseFactory->expects($this->any())->method('createResponse')->willReturn($response);

        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getGetUser()
            {
                return $this->getUser();
            }
        }
        )->setResponseFactory($responseFactory)->getGetUser();
    }

    public function testGetUser()
    {
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);

        self::assertEmpty(
            (new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }

    public function testGetUserBadToken()
    {
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);
        $storage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        self::assertEmpty(
            (new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }

    public function testGetUserUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturnCallback(fn() => new class() implements UserInterface {
                public function getPassword() {}
                public function getSalt() {}
                public function getUsername() {}
                public function getRoles(): array {}
                public function eraseCredentials(): void {}
                public function getUserIdentifier(): string {}
            });

        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);
        $storage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        self::assertInstanceOf(
            UserInterface::class,
            (new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }
}
