<?php
/**
 * East Framework.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Framework\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Framework\Controller\Controller;
use Teknoo\East\Framework\Http\ClientInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class ControllerTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
    public function testIsGrantedNoSession()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.authorization_checker')
            ->willReturn(false);

        (new class extends Controller {
            public function getIsGranted()
            {
                return $this->isGranted('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getisGranted();
    }

    public function testIsGranted()
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
                public function getIsGranted()
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
    public function testIsCsrfTokenValidNoManager()
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

    /**
     * @expectedException \LogicException
     */
    public function testGetDoctrineNoDoctrine()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('doctrine')
            ->willReturn(false);

        (new class extends Controller {
            public function getGetDoctrine()
            {
                return $this->GetDoctrine('foo', 'bar');
            }
        })->setContainer($this->getContainerMock())->getgetDoctrine();
    }

    public function testGetDoctrine()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('doctrine')
            ->willReturn(new \stdClass());

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('doctrine')
            ->willReturn(true);

        $this->assertInstanceOf(
            '\stdClass',
            (new class extends Controller {
                public function getGetDoctrine()
                {
                    return $this->getDoctrine('foo', 'bar');
                }
            })->setContainer($this->getContainerMock())->getgetDoctrine()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFormNoBundle()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('form.factory')
            ->willReturn(false);

        (new class extends Controller {
            public function getCreateForm()
            {
                return $this->createForm('foo');
            }
        })->setContainer($this->getContainerMock())->getcreateForm();
    }

    public function testCreateForm()
    {$builder = $this->getMock('Symfony\Component\Form\FormFactory', [], [], '', false);
        $builder->expects($this->any())
            ->method('create')
            ->willReturn($this->getMock('Symfony\Component\Form\Form', [], [], '', false));

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('form.factory')
            ->willReturn($builder);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('form.factory')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Symfony\Component\Form\Form',
            (new class extends Controller {
                public function getCreateForm()
                {
                    return $this->createForm('foo');
                }
            })->setContainer($this->getContainerMock())->getcreateForm()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFormBuilderNoBundle()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('form.factory')
            ->willReturn(false);

        (new class extends Controller {
            public function getCreateFormBuilder()
            {
                return $this->createFormBuilder();
            }
        })->setContainer($this->getContainerMock())->getcreateFormBuilder();
    }

    public function testCreateFormBuilder()
    {
        $builder = $this->getMock('Symfony\Component\Form\FormFactory', [], [], '', false);
        $builder->expects($this->any())
            ->method('createBuilder')
            ->willReturn($this->getMock('Symfony\Component\Form\FormBuilder', [], [], '', false));

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('form.factory')
            ->willReturn($builder);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('form.factory')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Symfony\Component\Form\FormBuilder',
            (new class extends Controller {
                public function getCreateFormBuilder()
                {
                    return $this->createFormBuilder();
                }
            })->setContainer($this->getContainerMock())->getcreateFormBuilder()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetUserNoStorage()
    {
        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(false);

        (new class extends Controller {
            public function getGetUser()
            {
                return $this->getUser();
            }
        })->setContainer($this->getContainerMock())->getgetUser();
    }

    public function testGetUser()
    {
        $storage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface', [], [], '', false);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($storage);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $this->assertEmpty(
            (new class extends Controller {
                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setContainer($this->getContainerMock())->getgetUser()
        );
    }

    public function testGetUserBadTocken()
    {
        $storage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface', [], [], '', false);
        $storage->expects($this->any())
            ->method('getToken')
            ->willReturn('fooBar');

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($storage);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $this->assertEmpty(
            (new class extends Controller {
                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setContainer($this->getContainerMock())->getgetUser()
        );
    }

    public function testGetUserBadEmptyUser()
    {
        $storage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface', [], [], '', false);
        $storage->expects($this->any())
            ->method('getToken')
            ->willReturn(new class {
                public function getUser(){
                    return null;
                }
            });

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($storage);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $this->assertEmpty(
            (new class extends Controller {
                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setContainer($this->getContainerMock())->getgetUser()
        );
    }

    public function testGetUserUser()
    {
        $storage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface', [], [], '', false);
        $storage->expects($this->any())
            ->method('getToken')
            ->willReturn(new class {
                public function getUser(){
                    return new class implements UserInterface{
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

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($storage);

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Symfony\Component\Security\Core\User\UserInterface',
            (new class extends Controller {
                public function getGetUser()
                {
                    return $this->getUser();
                }
            })->setContainer($this->getContainerMock())->getgetUser()
        );
    }

    public function testRenderTemplating()
    {
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        $client->expects($this->once())
            ->method('successfulResponseFromController')
            ->with($this->callback(function ($instance) {return $instance instanceof HtmlResponse;}))
            ->willReturnSelf();

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('templating')
            ->willReturn(new class {
                public function render($view, $parameters) {
                    return '<html>';
                }
            });

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->with('templating')
            ->willReturn(true);

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getRender(ClientInterface $client)
                {
                    return $this->render($client, 'routeName');
                }
            })->setContainer($this->getContainerMock())->getRender($client)
        );
    }

    public function testRenderTwig()
    {
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        $client->expects($this->once())
            ->method('successfulResponseFromController')
            ->with($this->callback(function ($instance) {return $instance instanceof HtmlResponse;}))
            ->willReturnSelf();

        $this->getContainerMock()
            ->expects($this->any())
            ->method('get')
            ->with('twig')
            ->willReturn(new class {
                public function render($view, $parameters) {
                    return '<html>';
                }
            });

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturnMap([['templating', false], ['twig', true]]);

        $this->assertInstanceOf(
            $this->getControllerClassName(),
            (new class extends Controller {
                public function getRender(ClientInterface $client)
                {
                    return $this->render($client, 'routeName');
                }
            })->setContainer($this->getContainerMock())->getRender($client)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRenderNoRendering()
    {
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        $client->expects($this->never())
            ->method('successfulResponseFromController');

        $this->getContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);


        (new class extends Controller {
            public function getRender(ClientInterface $client)
            {
                return $this->render($client, 'routeName');
            }
        })->setContainer($this->getContainerMock())->getRender($client);
    }
}