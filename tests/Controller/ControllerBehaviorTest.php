<?php

namespace Teknoo\Tests\East\Framework\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Teknoo\East\Framework\Controller\Controller;
use Teknoo\East\Framework\Http\ClientInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class ControllerTest
 * @package Teknoo\Tests\East\Framework\Controller
 * @covers Teknoo\East\Framework\Controller\Controller
 */
class ControllerBehaviorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerMock(): ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->getMock(
                'Symfony\Component\DependencyInjection\ContainerInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->container;
    }

    /**
     * @return Controller
     */
    private function buildController(): Controller
    {
        return new class extends Controller{};
    }

    /**
     * @return string
     */
    private function getControllerClassName(): string
    {
        return 'Teknoo\East\Framework\Controller\Controller';
    }

    public function testGenerateUrl()
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())
            ->method('generate')
            ->with('routeName', ['foo'=>'bar'])
            ->willReturn('/foo/bar');

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('router')
            ->willReturn($router);

        $this->assertEquals(
            '/foo/bar',
            (new class extends Controller {
                public function getUrl()
                {
                    return $this->generateUrl('routeName', ['foo'=>'bar']);
                }
            })->setContainer($this->getContainerMock())->getUrl()
        );
    }

    public function testRedirect()
    {
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        $client->expects($this->once())
            ->method('successfulResponseFromController')
            ->with($this->callback(function ($instance) {return $instance instanceof RedirectResponse;}))
            ->willReturnSelf();

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getRedirect(ClientInterface $client)
                {
                    return $this->redirect($client, 'routeName');
                }
            })->getRedirect($client)
        );
    }

    public function testRedirectToRoute()
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())
            ->method('generate')
            ->with('routeName', ['foo'=>'bar'])
            ->willReturn('/foo/bar');

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('router')
            ->willReturn($router);

        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        $client->expects($this->once())
            ->method('successfulResponseFromController')
            ->with($this->callback(function ($instance) {return $instance instanceof RedirectResponse;}))
            ->willReturnSelf();

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getRedirect(ClientInterface $client)
                {
                    return $this->redirectToRoute($client, 'routeName', ['foo'=>'bar']);
                }
            })->setContainer($this->getContainerMock())->getRedirect($client)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddFlashNoSession()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('session')
            ->willReturn(false);

        (new class extends Controller {
            public function getAddFlash()
            {
                return $this->addFlash('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getAddFlash();
    }

    public function testAddFlash()
    {
        $flash = $this->getMock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag');
        $flash->expects($this->any())
            ->method('add')
            ->willReturnSelf();

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->any())
            ->method('getFlashBag')
            ->willReturn($flash);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('session')
            ->willReturn($session);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('session')
            ->willReturn(true);

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getAddFlash()
                {
                    return $this->addFlash('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getAddFlash()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testisGrantedNoSession()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(false);

        (new class extends Controller {
            public function getisGranted()
            {
                return $this->isGranted('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getisGranted();
    }

    public function testisGranted()
    {
        $checker = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($checker);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(true);

        $this->assertTrue(
            (new class extends Controller {
                public function getisGranted()
                {
                    return $this->isGranted('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getisGranted()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testDenyAccessUnlessGrantedNoSession()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(false);

        (new class extends Controller {
            public function getDenyAccessUnlessGranted()
            {
                return $this->denyAccessUnlessGranted('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getDenyAccessUnlessGranted();
    }

    public function testDenyAccessUnlessGranted()
    {
        $checker = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($checker);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(true);

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getDenyAccessUnlessGranted()
                {
                    return $this->denyAccessUnlessGranted('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getDenyAccessUnlessGranted()
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    public function testDenyAccessUnlessGrantedError()
    {
        $checker = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($checker);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(true);

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getDenyAccessUnlessGranted()
                {
                    return $this->denyAccessUnlessGranted('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getDenyAccessUnlessGranted()
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testCreateNotFoundException()
    {
        (new class extends Controller {
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
        (new class extends Controller {
            public function getCreateAccessDeniedException()
            {
                throw $this->createAccessDeniedException();
            }
        })->getCreateAccessDeniedException();
    }

    public function testHas()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('fooBar')
            ->willReturn(true);

        $this->assertTrue(
            (new class extends Controller {
                public function getHas()
                {
                    return $this->has('fooBar');
                }
            })->setContainer($this->getContainerMock())->getHas()
        );
    }

    public function testGet()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('fooBar')
            ->willReturn(new \DateTime());

        $this->assertInstanceOf(
            '\DateTime',
            (new class extends Controller {
                public function getGet()
                {
                    return $this->get('fooBar');
                }
            })->setContainer($this->getContainerMock())->getGet()
        );
    }

    public function testGetParameter()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('getParameter')
            ->with('fooBar')
            ->willReturn(123);

        $this->assertEquals(
            123,
            (new class extends Controller {
                public function getGetParameter()
                {
                    return $this->getParameter('fooBar');
                }
            })->setContainer($this->getContainerMock())->getGetParameter()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsCsrfTokenValidNoSession()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.csrf.token_manager')
            ->willReturn(false);

        (new class extends Controller {
            public function getIsCsrfTokenValid()
            {
                return $this->isCsrfTokenValid('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getIsCsrfTokenValid();
    }

    public function testIsCsrfTokenValid()
    {
        $checker = $this->getMock('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $checker->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(true);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.csrf.token_manager')
            ->willReturn($checker);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.csrf.token_manager')
            ->willReturn(true);

        $this->assertTrue(
            (new class extends Controller {
                public function getIsCsrfTokenValid()
                {
                    return $this->isCsrfTokenValid('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getIsCsrfTokenValid()
        );
    }
}