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

use PHPUnit\Framework\MockObject\Stub;
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

use function base64_encode;
use function hash_hmac;
use function is_array;
use function json_encode;
use function ksort;

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
    private const string TEST_SECRET = 'foo';

    private ?UrlMatcherInterface $matcher = null;

    private ?ContainerInterface $container = null;

    private function getUrlMatcherMock(): UrlMatcherInterface&Stub
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createStub(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    private function getUrlMatcherMockObject(): UrlMatcherInterface&MockObject
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createMock(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    private function getContainerMock(): ContainerInterface&Stub
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->createStub(ContainerInterface::class);
        }

        return $this->container;
    }

    /**
     * @param array<string|int, string|array<string|int, string>> $data
     */
    private function recursiveKeySort(array &$data): void
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $this->recursiveKeySort($value);
            }
        }

        ksort($data);
    }

    /**
     * @param array<string|int, string|array<string|int, string>> $props
     */
    private function calculateChecksum(array $props): string
    {
        // sort so it is always consistent (frontend could have re-ordered data)
        $this->recursiveKeySort($props);

        return base64_encode(hash_hmac('sha256', (string) json_encode($props), self::TEST_SECRET, true));
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
            'ux_live_component',
            self::TEST_SECRET,
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
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
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/bar');
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
         */
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
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

        $this->getContainerMock()->method('has')->willReturn(true);
        $this->getContainerMock()->method('get')->willReturn(function () {
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
        $client = $this->createStub(ClientInterface::class);
        /**
         * @var ServerRequestInterface|MockObject $request
         */
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
        );
        /**
         * @var ManagerInterface|MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->method('has')->willReturn(true);
        $this->getContainerMock()->method('get')->willReturn(
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
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/app.php/foo');

        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDotPhpStart(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/app.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createStub(ManagerInterface::class);
        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo/app.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveAppDevDotPhpEnd(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/app_dev.php/foo');
        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveAppDevDotPhpEnd(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/app_dev.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createStub(ManagerInterface::class);
        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo/app_dev.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerRemoveIndexDotPhpStart(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/index.php/foo');
        $request->method('getUri')->willReturn($uri);
        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo')
            ->willReturn(['_controller' => function (): void {
            }]);

        $manager->method('continueExecution')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();
        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithControllerNotRemoveIndexDotPhpEnd(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);

        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo/index.php');
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createStub(ManagerInterface::class);
        $this->getUrlMatcherMockObject()->expects($this->once())->method('match')
            ->with('/foo/index.php')
            ->willReturn(['_controller' => function (): void {
            }]);

        $this->buildRouter()->execute($client, $request, $manager);
    }

    public function testExecuteWithMessage(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $message = $this->createStub(MessageInterface::class);
        $manager = $this->createStub(ManagerInterface::class);

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
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testExecuteErrorRequest(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRouter()->execute(
            $this->createStub(ClientInterface::class),
            new stdClass(),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testExecuteErrorManager(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRouter()->execute(
            $this->createStub(ClientInterface::class),
            $this->createStub(ServerRequestInterface::class),
            new stdClass()
        );
    }

    public function testExecuteWithPathMatchingExcludedPattern(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/api/health');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->never())->method('updateMessage');

        $this->getUrlMatcherMockObject()->expects($this->never())->method('match');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter(['/api', '/_wdt'])->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithUpdatedFields(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/user/profile/456'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props,
                'updated' => [
                    'username' => 'john_doe',
                    'email' => 'john@example.com',
                ]
            ])
        ]);
        $request->method('getAttributes')->willReturn([]);

        $callIndex = 0;
        $request->expects($this->exactly(6))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$callIndex) {
                $expectedCalls = [
                    ['username', 'john_doe'],
                    ['email', 'john@example.com'],
                ];

                if ($callIndex < 2) {
                    $this->assertEquals($expectedCalls[$callIndex][0], $key);
                    $this->assertEquals($expectedCalls[$callIndex][1], $value);
                }

                $callIndex++;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserProfile',
                    ];
                }
                if ($path === '/user/profile/456') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '456',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use ($manager) {
                $this->assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithNonStringData(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getParsedBody')->willReturn([
            'data' => 12345  // Not a string
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_route' => 'ux_live_component',
            '_live_component' => 'UserProfile',
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithoutOriginalPath(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => []  // No originalPath
            ])
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_route' => 'ux_live_component',
            '_live_component' => 'UserProfile',
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithoutProps(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([])  // No props
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_route' => 'ux_live_component',
            '_live_component' => 'UserProfile',
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteCleaningAppPhp(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/app.php/user/profile/789'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
            ])
        ]);
        $request->method('getAttributes')->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserProfile',
                    ];
                }
                if ($path === '/user/profile/789') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '789',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use ($manager) {
                $this->assertInstanceOf(ResultInterface::class, $workPlan[ResultInterface::class]);
                return $manager;
            });
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveParametersAddsAttributes(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/product/view/999'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
            ])
        ]);
        $request->method('getAttributes')->willReturn([]);

        $addedAttributes = [];
        // All parameters: _controller, id, category, _live_parameters, _live_body = 5 attributes
        $request->expects($this->exactly(5))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$addedAttributes) {
                $addedAttributes[$key] = $value;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'ProductView',
                    ];
                }
                if ($path === '/product/view/999') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '999',
                        'category' => 'electronics',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );

        // Verify the expected attributes were added (including _live_parameters)
        $this->assertArrayHasKey('_controller', $addedAttributes);
        $this->assertArrayHasKey('id', $addedAttributes);
        $this->assertEquals('999', $addedAttributes['id']);
        $this->assertArrayHasKey('category', $addedAttributes);
        $this->assertEquals('electronics', $addedAttributes['category']);
        $this->assertArrayHasKey('_live_parameters', $addedAttributes);
        $this->assertArrayHasKey('_live_body', $addedAttributes);
    }

    public function testExecuteWithNonCallableObjectController(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            $this->createStub(UriInterface::class)
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $nonCallableObject = new stdClass();

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_controller' => $nonCallableObject
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithUpdatedFieldsNonStringKey(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/user/profile/111'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props,
                'updated' => [
                    123 => 'should_be_ignored',  // Non-string key
                    'validKey' => 'validValue',
                ]
            ])
        ]);
        $request->method('getAttributes')->willReturn([]);

        $callIndex = 0;
        $request->expects($this->exactly(5))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$callIndex) {
                if ($callIndex === 0) {
                    $this->assertEquals('validKey', $key);
                    $this->assertEquals('validValue', $value);
                }
                // The key 123 should never appear
                $this->assertIsString($key);
                $callIndex++;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserProfile',
                    ];
                }
                if ($path === '/user/profile/111') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '111',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithUpdatedFieldsExistingAttribute(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/user/profile/333'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props,
                'updated' => [
                    'existingAttr' => 'should_not_overwrite',
                    'newAttr' => 'should_be_added',
                ]
            ])
        ]);

        // Configure getAttribute to return the existing value for existingAttr
        $request->method('getAttribute')->willReturnCallback(
            function (string $key, $default = null) {
                if ($key === 'existingAttr') {
                    return 'original_value';
                }
                return $default;
            }
        );

        // Only 'newAttr' from 'updated' should be added (existingAttr already exists)
        // + 4 from _live_parameters (_controller, id, _live_parameters, _live_body)
        $callIndex = 0;
        $request->expects($this->exactly(5))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$callIndex) {
                // First call should be newAttr from 'updated'
                if ($callIndex === 0) {
                    $this->assertEquals('newAttr', $key);
                    $this->assertEquals('should_be_added', $value);
                }
                // existingAttr should never be overwritten
                if ($key === 'existingAttr' && $value === 'should_not_overwrite') {
                    $this->fail('existingAttr should not be overwritten');
                }
                $callIndex++;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserProfile',
                    ];
                }
                if ($path === '/user/profile/333') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '333',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteWithoutUpdatedField(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/user/profile/444'
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
                // No 'updated' field
            ])
        ]);
        $request->method('getAttributes')->willReturn([]);

        // 0 from 'updated' (no updated field) + 4 from _live_parameters (all params)
        $request->expects($this->exactly(4))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request) {
                // Only _live_parameters attributes should be added
                $this->assertContains($key, ['_controller', 'id', '_live_parameters', '_live_body']);
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserProfile',
                    ];
                }
                if ($path === '/user/profile/444') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '444',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithNormalRequestNoLiveParametersNoAttributesAdded(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/normal/route');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        // No withAttribute should be called for normal requests
        $request->expects($this->never())->method('withAttribute');

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_controller' => function (): void {},
            'id' => '666',
            'name' => 'test',
            // No _live_parameters
        ]);

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteMissingChecksum(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);


        // Props without checksum
        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => [
                    'originalPath' => '/user/profile/555'
                ]
            ])
        ]);

        // No withAttribute should be called for normal requests
        $request->expects($this->never())->method('withAttribute');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_route' => 'ux_live_component',
            '_live_component' => 'UserProfile',
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteInvalidChecksum(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        // Props with invalid checksum
        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => [
                    'originalPath' => '/user/profile/666',
                    '@checksum' => 'invalid_checksum_value'
                ]
            ])
        ]);

        // No withAttribute should be called for normal requests
        $request->expects($this->never())->method('withAttribute');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getUrlMatcherMock()->method('match')->willReturn([
            '_route' => 'ux_live_component',
            '_live_component' => 'UserProfile',
        ]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->execute($client, $request, $manager)
        );
    }

    public function testExecuteWithLiveComponentRouteScalarPropsCopied(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/product/list/123',
            'userId' => 'user_456',
            'pageNumber' => 2,
            'isActive' => true,
            'discount' => 15.5,
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
            ])
        ]);

        $request->method('getAttribute')->willReturn(null);

        // Expected calls: userId, pageNumber, isActive, discount (scalar props)
        // + _controller, id, _live_parameters, _live_body (from live parameters) = 8 total
        $addedAttributes = [];
        $request->expects($this->exactly(8))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$addedAttributes) {
                $addedAttributes[$key] = $value;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'ProductList',
                    ];
                }
                if ($path === '/product/list/123') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '123',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->buildRouter()->execute($client, $request, $manager);

        // Verify scalar props were copied
        $this->assertArrayHasKey('userId', $addedAttributes);
        $this->assertEquals('user_456', $addedAttributes['userId']);
        $this->assertArrayHasKey('pageNumber', $addedAttributes);
        $this->assertEquals(2, $addedAttributes['pageNumber']);
        $this->assertArrayHasKey('isActive', $addedAttributes);
        $this->assertTrue($addedAttributes['isActive']);
        $this->assertArrayHasKey('discount', $addedAttributes);
        $this->assertEquals(15.5, $addedAttributes['discount']);

        // Verify originalPath was NOT copied
        $this->assertArrayNotHasKey('originalPath', $addedAttributes);
    }

    public function testExecuteWithLiveComponentRouteNonScalarPropsNotCopied(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/product/view/789',
            'scalarProp' => 'validValue',
            'arrayProp' => ['item1', 'item2'],
            'objectProp' => (object)['key' => 'value'],
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
            ])
        ]);

        $request->method('getAttribute')->willReturn(null);

        // Only scalarProp should be copied from props
        // + _controller, id, _live_parameters, _live_body = 5 total
        $addedAttributes = [];
        $request->expects($this->exactly(5))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$addedAttributes) {
                $addedAttributes[$key] = $value;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'ProductView',
                    ];
                }
                if ($path === '/product/view/789') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '789',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->buildRouter()->execute($client, $request, $manager);

        // Verify only scalar prop was copied
        $this->assertArrayHasKey('scalarProp', $addedAttributes);
        $this->assertEquals('validValue', $addedAttributes['scalarProp']);

        // Verify non-scalar props were NOT copied
        $this->assertArrayNotHasKey('arrayProp', $addedAttributes);
        $this->assertArrayNotHasKey('objectProp', $addedAttributes);
    }

    public function testExecuteWithLiveComponentRouteSpecialPrefixedPropsIgnored(): void
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/_components');

        $client = $this->createStub(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $props = [
            'originalPath' => '/user/settings/999',
            'normalProp' => 'shouldBeCopied',
            '@internalProp' => 'shouldBeIgnored',
            '_privateProp' => 'shouldBeIgnored',
        ];
        $props['@checksum'] = $this->calculateChecksum($props);

        $request->method('getParsedBody')->willReturn([
            'data' => json_encode([
                'props' => $props
            ])
        ]);

        $request->method('getAttribute')->willReturn(null);

        // Only normalProp should be copied from props
        // + _controller, id, _live_parameters, _live_body = 5 total
        $addedAttributes = [];
        $request->expects($this->exactly(5))->method('withAttribute')
            ->willReturnCallback(function (string $key, $value) use ($request, &$addedAttributes) {
                $addedAttributes[$key] = $value;
                return $request;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->getUrlMatcherMock()->method('match')->willReturnCallback(
            function (string $path) {
                if ($path === '/_components') {
                    return [
                        '_route' => 'ux_live_component',
                        '_live_component' => 'UserSettings',
                    ];
                }
                if ($path === '/user/settings/999') {
                    return [
                        '_controller' => function (): void {},
                        'id' => '999',
                    ];
                }
                return [];
            }
        );

        $manager->expects($this->once())->method('updateWorkPlan')->willReturnSelf();
        $manager->expects($this->once())->method('updateMessage')->willReturnSelf();

        $this->buildRouter()->execute($client, $request, $manager);

        // Verify normal prop was copied
        $this->assertArrayHasKey('normalProp', $addedAttributes);
        $this->assertEquals('shouldBeCopied', $addedAttributes['normalProp']);

        // Verify special prefixed props were NOT copied
        $this->assertArrayNotHasKey('@internalProp', $addedAttributes);
        $this->assertArrayNotHasKey('_privateProp', $addedAttributes);
        $this->assertArrayNotHasKey('originalPath', $addedAttributes);
    }
}
