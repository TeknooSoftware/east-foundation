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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBunlde\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\FoundationBundle\Controller\EastControllerTrait;
use Teknoo\East\Foundation\Http\ClientInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class ControllerTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Controller\EastControllerTrait
 */
class EastControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testGenerateUrlMissingRouter()
    {
        self::assertEquals(
            '/foo/bar',
            (new class() {
                use EastControllerTrait;

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
                use EastControllerTrait;

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
            ->method('responseFromController')
            ->with($this->callback(function ($instance) {
                return $instance instanceof RedirectResponse;
            }))
            ->willReturnSelf();

        $controller = (new class() {
            use EastControllerTrait;
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
            ->method('responseFromController')
            ->with($this->callback(function ($instance) {
                return $instance instanceof RedirectResponse;
            }))
            ->willReturnSelf();

        $controller = (new class() {
            use EastControllerTrait;

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
            ->method('responseFromController')
            ->with($this->callback(function ($instance) {
                return $instance instanceof HtmlResponse;
            }))
            ->willReturnSelf();

        $twigEngine = $this->createMock(TwigEngine::class);
        $twigEngine->expects(self::once())->method('render')->willReturn('fooBar');

        $controller = (new class() {
            use EastControllerTrait;

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

    public function testRenderTwig()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('responseFromController')
            ->with($this->callback(function ($instance) {
                return $instance instanceof HtmlResponse;
            }))
            ->willReturnSelf();

        $controller = (new class() {
            use EastControllerTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        });

        $twig = $this->createMock(\Twig_Environment::class);
        $twig->expects(self::once())->method('render')->willReturn('fooBar');

        self::assertInstanceOf(
            get_class($controller),
            $controller->setTwig($twig)->getRender($client)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRenderNoRendering()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('responseFromController');

        (new class() {
            use EastControllerTrait;

            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        })->getRender($client);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testCreateNotFoundException()
    {
        (new class() {
            use EastControllerTrait;

            public function getCreateNotFoundException()
            {
                throw $this->createNotFoundException();
            }
        })->getCreateNotFoundException();
    }

    /**
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    public function testCreateAccessDeniedException()
    {
        (new class() {
            use EastControllerTrait;

            public function getCreateAccessDeniedException()
            {
                throw $this->createAccessDeniedException();
            }
        })->getCreateAccessDeniedException();
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetUserNoStorage()
    {
        (new class() {
            use EastControllerTrait;

            public function getGetUser()
            {
                return $this->getUser();
            }
        })->getGetUser();
    }

    public function testGetUser()
    {
        $storage = $this->createMock(TokenStorageInterface::class, [], [], '', false);

        self::assertEmpty(
            (new class() {
                use EastControllerTrait;

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
            (new class() {
                use EastControllerTrait;

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
            ->willReturn(new class() {
                public function getUser()
                {
                    return null;
                }
            });

        self::assertEmpty(
            (new class() {
                use EastControllerTrait;

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
            ->willReturn(new class() {
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
            (new class() {
                use EastControllerTrait;

                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setTokenStorage($storage)->getGetUser()
        );
    }
}
