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

namespace Teknoo\Tests\East\FoundationBundle\Router;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\FoundationBundle\Router\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Class RouterTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Router::class)]
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
     * @return UrlMatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getUrlMatcherMock(): UrlMatcherInterface
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createMock(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    /**
     * @return ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
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
    private function buildRouter(array $excludedPaths = []): Router
    {
        return new Router(
            $this->getUrlMatcherMock(),
            $this->getContainerMock(),
            $excludedPaths,
        );
    }

    private function getRouterClass(): string
    {
        return Router::class;
    }

    public function testExecuteNotFound()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn([]);

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteNotFoundException()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willThrowException(new ResourceNotFoundException());

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteOtherException()
    {
        $this->expectException(\Exception::class);
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willThrowException(new \Exception());

        $manager->expects($this->never())->method('continueExecution');

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithNoController()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithNoPathExcluded()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter(['/.foo'])->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithNoStartPathExcluded()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo/bar');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter(['/foo'])->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerNotCallable()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => 'foo::bar']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithController()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => function () {
        }]);

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $manager->expects($this->once())->method('updateWorkPlan')
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
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function ($workPlan) use ($manager) {
                self::assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $class = new class {
            public static function action()
            {
            }
        };

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerSymfonyStyleNotStatic()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class {
            public function action()
            {
            }
        };

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerSymfonyStyleNotFound()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class {
            public function action()
            {
            }
        };

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => $class::class.'::action2']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithSymfonyAbstractControllerStatic()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class extends SymfonyAbstractController {
            public static function action()
            {
            }
        };

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->expects($this->never())->method('continueExecution');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerInContainer()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function ($workPlan) use ($manager) {
                self::assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects($this->any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects($this->any())->method('get')->with('fooBar')->willReturn(function () {
        });

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithSymfonyAbstractControllerInContainer()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects($this->any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects($this->any())->method('get')->with('fooBar')->willReturn(
            new class extends SymfonyAbstractController {
            }
        );

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerRemoveAppDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/app.php/foo');

        $request->expects($this->any())->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
            }]);

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo/app.php');
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/app.php')
            ->willReturn(['_controller' => function () {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveAppDevDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/app_dev.php/foo');
        $request->expects($this->any())->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
            }]);

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDevDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo/app_dev.php');
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/app_dev.php')
            ->willReturn(['_controller' => function () {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveIndexDotPhpStart()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/index.php/foo');
        $request->expects($this->any())->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function () {
            }]);

        $manager->expects($this->any())->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveIndexDotPhpEnd()
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo/index.php');
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/index.php')
            ->willReturn(['_controller' => function () {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithMessage()
    {
        $client = $this->createMock(ClientInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        self::assertInstanceOf(
            RouterInterface::class,
            $this->buildRouter()->execute($client, $message, $manager)
        );
    }

    public function testExecuteErrorClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildRouter()->execute(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteErrorRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteErrorManager()
    {
        $this->expectException(\TypeError::class);
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }
}
