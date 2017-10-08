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
use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
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
        return new Manager(new Queue());
    }

    /**
     * @return string
     */
    public function getManagerClass(): string
    {
        return Manager::class;
    }

    public function testReceiveRequest()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->receiveRequest(
                $this->createMock(ClientInterface::class),
                $this->createMock(ServerRequestInterface::class)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestErrorClient()
    {
        $this->buildManager()->receiveRequest(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestErrorRequest()
    {
        $this->buildManager()->receiveRequest(
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

    public function testStop()
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

            public function execute(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $this->testSuite->assertInstanceOf(
                    $this->testSuite->getManagerClass(),
                    $manager->stop()
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
        $manager->receiveRequest($clientMock, $serverRequestMock);
    }

    public function testBehaviorReceiveRequest()
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
        $middleware1->expects(self::once())->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('execute');
        $middleware2->expects(self::once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('execute');

        $manager->registerMiddleware($middleware1);
        $manager->registerMiddleware($middleware2);
        $manager->registerMiddleware($middleware3);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }

    public function testBehaviorReceiveRequestWithPriorityWithStop()
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
        $middleware1->expects(self::never())->method('execute');

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('execute');
        $middleware2->expects(self::once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stop();
                $callList[] = 'middleware2';

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('execute');

        $manager->registerMiddleware($middleware1, 2);
        $manager->registerMiddleware($middleware2, 1);
        $manager->registerMiddleware($middleware3, 2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2'], $callList);
    }

    public function testBehaviorReceiveRequestWithPriority()
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
        $middleware1->expects(self::once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('execute');
        $middleware2->expects(self::once())->method('execute')->willReturnCallback(
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
        $middleware3->expects(self::never())->method('execute');

        $manager->registerMiddleware($middleware1, 2);
        $manager->registerMiddleware($middleware2, 1);
        $manager->registerMiddleware($middleware3, 2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveRequestAndContinueExecution()
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
        $middleware1->expects(self::once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::once())->method('execute');
        $middleware2->expects(self::once())->method('execute')->willReturnCallback(
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
        $middleware3->expects(self::never())->method('execute');

        $manager->registerMiddleware($middleware1, 2);
        $manager->registerMiddleware($middleware2, 1);
        $manager->registerMiddleware($middleware3, 2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorMultipleReceiveRequest()
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
        $middleware1->expects(self::exactly(2))->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects(self::exactly(2))->method('execute');
        $middleware2->expects(self::exactly(2))->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects(self::never())->method('execute');

        $manager->registerMiddleware($middleware1);
        $manager->registerMiddleware($middleware2);
        $manager->registerMiddleware($middleware3);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testSetQueueBadType()
    {
        $this->buildManager()->setQueue(
            new \stdClass()
        );
    }

    public function testSetQueue()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->setQueue(
                $this->createMock(QueueInterface::class)
            )
        );
    }
}
