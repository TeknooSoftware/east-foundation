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

namespace Teknoo\Tests\East\Foundation\Manager;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

/**
 * Class ManagerTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Manager\Manager
 * @covers \Teknoo\East\Foundation\Manager\States\Running
 * @covers \Teknoo\East\Foundation\Manager\States\Service
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Manager
     */
    private function buildManager(): Manager
    {
        return new Manager();
    }

    /**
     * @return string
     */
    public function getManagerClass(): string
    {
        return Manager::class;
    }

    public function testReceiveRequestFromClient()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->receiveRequestFromClient(
                $this->createMock(ClientInterface::class),
                $this->createMock(ServerRequestInterface::class)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromClientErrorClient()
    {
        $this->buildManager()->receiveRequestFromClient(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromClientErrorRequest()
    {
        $this->buildManager()->receiveRequestFromClient(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testContinueExecutionErrorClient()
    {
        $this->buildManager()->continueExecution(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testContinueExecutionErrorRequest()
    {
        $this->buildManager()->continueExecution(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testRegisterMiddleware()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->registerMiddleware(
                $this->createMock(MiddlewareInterface::class)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testRegisterMiddlewareError()
    {
        $this->buildManager()->registerMiddleware(new \stdClass());
    }

    public function testStopPropagation()
    {
        $middleware = new class($this) implements MiddlewareInterface {
            /**
             * @var ManagerTest
             */
            private $testSuite;

            public function __construct(ManagerTest $that)
            {
                $this->testSuite = $that;
            }

            public function executeRequestFromManager(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $this->testSuite->assertInstanceOf(
                    $this->testSuite->getManagerClass(),
                    $manager->stopPropagation()
                );

                return $this;
            }
        };

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $manager = $this->buildManager();
        $manager->registerMiddleware($middleware);
        $manager->receiveRequestFromClient($clientMock, $serverRequestMock);
    }

    public function testBehaviorReceiveRequestFromClient()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects(self::once())->method('executeRequestFromManager')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('executeRequestFromManager');
        $middleware2->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('executeRequestFromManager');

        $manager->registerMiddleware($middleware1);
        $manager->registerMiddleware($middleware2);
        $manager->registerMiddleware($middleware3);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );
    }

    public function testBehaviorReceiveRequestFromClientWithPriorityWithStopPropagation()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects(self::never())->method('executeRequestFromManager');

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('executeRequestFromManager');
        $middleware2->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
                $callList[] = 'middleware2';

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('executeRequestFromManager');

        $manager->registerMiddleware($middleware1,2);
        $manager->registerMiddleware($middleware2,1);
        $manager->registerMiddleware($middleware3,2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2'], $callList);
    }

    public function testBehaviorReceiveRequestFromClientWithPriority()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stopPropagation();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('executeRequestFromManager');
        $middleware2->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $callList[] = 'middleware2';

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('executeRequestFromManager');

        $manager->registerMiddleware($middleware1,2);
        $manager->registerMiddleware($middleware2,1);
        $manager->registerMiddleware($middleware3,2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveRequestFromClientAndContinueExecution()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stopPropagation();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('executeRequestFromManager');
        $middleware2->expects(self::once())->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $callList[] = 'middleware2';

                $managerPassed->continueExecution($clientPassed, $serverRequestMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('executeRequestFromManager');

        $manager->registerMiddleware($middleware1,2);
        $manager->registerMiddleware($middleware2,1);
        $manager->registerMiddleware($middleware3,2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorMultipleReceiveRequestFromClient()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects(self::exactly(2))->method('executeRequestFromManager')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::exactly(2))->method('executeRequestFromManager');
        $middleware2->expects(self::exactly(2))->method('executeRequestFromManager')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('executeRequestFromManager');

        $manager->registerMiddleware($middleware1);
        $manager->registerMiddleware($middleware2);
        $manager->registerMiddleware($middleware3);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );
    }
}
