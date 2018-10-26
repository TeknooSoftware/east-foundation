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

namespace Teknoo\Tests\East\FoundationBundle\Router;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\FoundationBundle\Router\Router;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Zend\Diactoros\Uri;

/**
 * Class RouterTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Router\Router
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return UrlMatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUrlMatcherMock(): UrlMatcherInterface
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createMock(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerMock(): ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->createMock(ContainerInterface::class);
        }

        return $this->container;
    }

    /**
     * @return Router
     */
    private function buildRouter(): Router
    {
        return new Router(
            $this->getUrlMatcherMock(),
            $this->getContainerMock()
        );
    }

    /**
     * @return string
     */
    private function getRouterClass(): string
    {
        return Router::class;
    }

    public function testExecuteNotFound()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn([]);

        $manager->expects(self::never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteNotFoundException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willThrowException(new ResourceNotFoundException());

        $manager->expects(self::never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteOtherException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willThrowException(new \Exception());

        $manager->expects(self::never())->method('continueExecution');

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithNoController()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['foo', 'bar']);

        $manager->expects(self::never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerNotCallable()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'foo::bar']);

        $manager->expects(self::never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerInContainerNotCallable()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects(self::any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects(self::any())->method('get')->with('fooBar')->willReturn('foo::bar');


        $manager->expects(self::never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithController()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => function () {
        }]);

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();
        $manager->expects(self::once())->method('updateWorkPlan')
            ->willReturnCallback(function ($workPlan) use ($manager) {
                self::assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerStatic()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')
            ->willReturnCallback(function ($workPlan) use ($manager) {
                self::assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $class = new class {
            public static function action()
            {
            }
        };

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => \get_class($class).'::action']);

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerInCOntainer()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')
            ->willReturnCallback(function ($workPlan) use ($manager) {
                self::assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects(self::any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects(self::any())->method('get')->with('fooBar')->willReturn(function () {
        });

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerRemoveAppDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/app.php/foo';
            }
        });
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
        }]);

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/foo/app.php';
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo/app.php')
            ->willReturn(['_controller' => function () {
        }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveAppDevDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/app_dev.php/foo';
            }
        });
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
        }]);

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDevDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/foo/app_dev.php';
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo/app_dev.php')
            ->willReturn(['_controller' => function () {
        }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveIndexDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/index.php/foo';
            }
        });
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
        }]);

        $manager->expects(self::once())->method('continueExecution')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveIndexDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
            public function getPath() {
                return '/foo/index.php';
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects(self::once())->method('match')
            ->with('/foo/index.php')
            ->willReturn(['_controller' => function () {
        }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteErrorClient()
    {
        $this->buildRouter()->execute(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteErrorRequest()
    {
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteErrorManager()
    {
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }
}
