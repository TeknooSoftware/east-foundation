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
use Teknoo\East\Foundation\Router\RouterInterface;

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
 * @covers \Teknoo\East\Foundation\Manager\States\HadRun
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

    public function testRegisterRouter()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->registerRouter(
                $this->createMock(RouterInterface::class)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testRegisterRouterError()
    {
        $this->buildManager()->registerRouter(new \stdClass());
    }

    public function testUnregisterRouter()
    {
        $router = $this->createMock(RouterInterface::class);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->unregisterRouter(
                $router
            )
        );

        $router = $this->createMock(RouterInterface::class);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->registerRouter($router)->unregisterRouter($router)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testUnregisterRouterError()
    {
        $this->buildManager()->unregisterRouter(new \stdClass());
    }

    public function testStopPropagation()
    {
        $router = new class($this) implements RouterInterface {
            /**
             * @var ManagerTest
             */
            private $testSuite;

            public function __construct(ManagerTest $that)
            {
                $this->testSuite = $that;
            }

            public function receiveRequestFromServer(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): RouterInterface {
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
        $manager->registerRouter($router);
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
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router1 = $this->createMock(RouterInterface::class);
        $router1->expects(self::once())->method('receiveRequestFromServer')->willReturnSelf();
        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router2 = $this->createMock(RouterInterface::class);
        $router2->expects(self::once())->method('receiveRequestFromServer');
        $router2->expects(self::once())->method('receiveRequestFromServer')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
            }
        );

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router3 = $this->createMock(RouterInterface::class);
        $router3->expects(self::never())->method('receiveRequestFromServer');

        $manager->registerRouter($router1);
        $manager->registerRouter($router2);
        $manager->registerRouter($router3);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );
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
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router1 = $this->createMock(RouterInterface::class);
        $callList = [];
        $router1->expects(self::once())->method('receiveRequestFromServer')->willReturnCallback(
            function () use (&$callList, $router1) {
                $callList[] = 'router1';

                return $router1;
            }
        );
        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router2 = $this->createMock(RouterInterface::class);
        $router2->expects(self::once())->method('receiveRequestFromServer');
        $router2->expects(self::once())->method('receiveRequestFromServer')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $router2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
                $callList[] = 'router2';

                return $router2;
            }
        );

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router3 = $this->createMock(RouterInterface::class);
        $router3->expects(self::never())->method('receiveRequestFromServer');

        $manager->registerRouter($router1,2);
        $manager->registerRouter($router2,1);
        $manager->registerRouter($router3,2);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock, $serverRequestMock)
        );

        self::assertEquals(['router2','router1'], $callList);
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
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router1 = $this->createMock(RouterInterface::class);
        $router1->expects(self::exactly(2))->method('receiveRequestFromServer')->willReturnSelf();
        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router2 = $this->createMock(RouterInterface::class);
        $router2->expects(self::exactly(2))->method('receiveRequestFromServer');
        $router2->expects(self::exactly(2))->method('receiveRequestFromServer')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                self::assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
            }
        );

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $router3 = $this->createMock(RouterInterface::class);
        $router3->expects(self::never())->method('receiveRequestFromServer');

        $manager->registerRouter($router1);
        $manager->registerRouter($router2);
        $manager->registerRouter($router3);
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
