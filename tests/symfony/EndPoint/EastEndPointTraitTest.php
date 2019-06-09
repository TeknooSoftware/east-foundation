<?php
/**
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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBunlde\EndPoint;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Foundation\Http\ClientInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class ControllerTest.
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait
 */
class EastEndPointTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerateUrlMissingRouter()
    {
        $this->expectException(\LogicException::class);
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
        $router->expects(self::any())
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
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($instance) {
                return $instance instanceof RedirectResponse;
            }))
            ->willReturnSelf();

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;
            public function getRedirect(ClientInterface $client)
            {
                return $this->redirect($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            get_class($controller),
            $controller->getRedirect($client)
        );
    }

    public function testRedirectToRoute()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::any())
            ->method('generate')
            ->with('routeName', ['foo' => 'bar'])
            ->willReturn('/foo/bar');

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($instance) {
                return $instance instanceof RedirectResponse;
            }))
            ->willReturnSelf();

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRedirect(ClientInterface $client)
            {
                return $this->redirectToRoute($client, 'routeName', ['foo' => 'bar']);
            }
        });

        self::assertInstanceOf(
            get_class($controller),
            $controller->setRouter($router)->getRedirect($client)
        );
    }

    public function testRenderTemplating()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($instance) {
                return $instance instanceof HtmlResponse && $instance->getBody()->getContents();
            }))
            ->willReturnSelf();

        $twigEngine = $this->createMock(EngineInterface::class);
        $twigEngine->expects(self::once())->method('render')->willReturn('fooBar');

        $controller = (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        });

        self::assertInstanceOf(
            get_class($controller),
            $controller->setTemplating($twigEngine)->getRender($client)
        );
    }

    public function testRenderNoRendering()
    {
        $this->expectException(\LogicException::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->willReturnCallback(function ($instance) use ($client) {
                $instance instanceof HtmlResponse && $instance->getBody()->getContents();

                return $client;
            });

        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        }
        )->getRender($client);
    }

    public function testCreateNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);
        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateNotFoundException()
            {
                throw $this->createNotFoundException();
            }
        }
        )->getCreateNotFoundException();
    }

    public function testCreateAccessDeniedException()
    {
        $this->expectException(AccessDeniedHttpException::class);
        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getCreateAccessDeniedException()
            {
                throw $this->createAccessDeniedException();
            }
        }
        )->getCreateAccessDeniedException();
    }

    public function testGetUserNoStorage()
    {
        $this->expectException(\LogicException::class);
        (new class() implements EndPointInterface {
            use EastEndPointTrait;

            public function getGetUser()
            {
                return $this->getUser();
            }
        }
        )->getGetUser();
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

    public function testGetUserBadTocken()
    {
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);
        $storage->expects(self::any())
            ->method('getToken')
            ->willReturn('fooBar');

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

    public function testGetUserBadEmptyUser()
    {
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);
        $storage->expects(self::any())
            ->method('getToken')
            ->willReturn(new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getUser()
                {
                    return null;
                }
            });

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
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);
        $storage->expects(self::any())
            ->method('getToken')
            ->willReturn(new class() implements EndPointInterface {
                use EastEndPointTrait;

                public function getUser()
                {
                    return new class() implements UserInterface {
                        public function getRoles()
                        {
                        }

                        public function getPassword()
                        {
                        }

                        public function getSalt()
                        {
                        }

                        public function getUsername()
                        {
                        }

                        public function eraseCredentials()
                        {
                        }
                    };
                }
            });

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
