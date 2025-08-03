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

namespace Teknoo\Tests\East\FoundationBundle\Router;

use PHPUnit\Framework\TestCase;
use Exception;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
    private ?UrlMatcherInterface $matcher = null;

    private ?ContainerInterface $container = null;

    private function getUrlMatcherMock(): UrlMatcherInterface&MockObject
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createMock(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    private function getContainerMock(): ContainerInterface&MockObject
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

    public function testExecuteNotFound(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn([]);

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteNotFoundException(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willThrowException(new ResourceNotFoundException());

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteOtherException(): void
    {
        $this->expectException(Exception::class);
        /**
         * @var ClientInterface|MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willThrowException(new \Exception());

        $manager->expects($this->never())->method('continueExecution');

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithNoController(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithNoPathExcluded(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter(['/.foo'])->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithNoStartPathExcluded(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/bar');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn(['foo', 'bar']);

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter(['/foo'])->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerNotCallable(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => 'foo::bar']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithController(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => function () {
        }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use ($manager) {
                $this->assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerStatic(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use ($manager) {
                $this->assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $class = new class () {
            public static function action(): void
            {
            }
        };

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerSymfonyStyleNotStatic(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class () {
            public function action(): void
            {
            }
        };

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerSymfonyStyleNotFound(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class () {
            public function action(): void
            {
            }
        };

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => $class::class.'::action2']);

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithSymfonyAbstractControllerStatic(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $class = new class () extends SymfonyAbstractController {
            public static function action(): void
            {
            }
        };

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => $class::class.'::action']);

        $manager->expects($this->never())->method('continueExecution');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerInContainer(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use ($manager) {
                $this->assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->method('get')->with('fooBar')->willReturn(function () {
        });

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithSymfonyAbstractControllerInContainer(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createMock(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->method('get')->with('fooBar')->willReturn(
            new class () extends SymfonyAbstractController {
            }
        );

        $manager->expects($this->never())->method('continueExecution')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithControllerRemoveAppDotPhpStart(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/app.php/foo');

        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDotPhpStart(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/app.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/app.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveAppDevDotPhpEnd(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/app_dev.php/foo');
        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDevDotPhpEnd(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/app_dev.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/app_dev.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveIndexDotPhpStart(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/index.php/foo');
        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveIndexDotPhpEnd(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/index.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $this->getUrlMatcherMock()->expects($this->once())->method('match')
            ->with('/foo/index.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithMessage(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $this->assertInstanceOf(
            RouterInterface::class,
            $this->buildRouter()->execute($client, $message, $manager)
        );
    }

    public function testExecuteErrorClient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRouter()->execute(
            new stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteErrorRequest(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            new stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteErrorManager(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRouter()->execute(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new stdClass()
        );
    }
}
