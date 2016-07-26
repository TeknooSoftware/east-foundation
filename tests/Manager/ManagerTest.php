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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Framework\Manager;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\Manager\Manager;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Teknoo\East\Framework\Router\RouterInterface;

/**
 * Class ManagerTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers Teknoo\East\Framework\Manager\Manager
 * @covers Teknoo\East\Framework\Manager\Manager\States\HadRun
 * @covers Teknoo\East\Framework\Manager\Manager\States\Running
 * @covers Teknoo\East\Framework\Manager\Manager\States\Service
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
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
        return 'Teknoo\East\Framework\Manager\Manager';
    }

    public function testReceiveRequestFromClient()
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->receiveRequestFromClient(
                $this->createMock('Teknoo\East\Framework\Http\ClientInterface'),
                $this->createMock('Psr\Http\Message\ServerRequestInterface')
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
            $this->createMock('Psr\Http\Message\ServerRequestInterface')
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromClientErrorRequest()
    {
        $this->buildManager()->receiveRequestFromClient(
            $this->createMock('Teknoo\East\Framework\Http\ClientInterface'),
            new \stdClass()
        );
    }

    public function testRegisterRouter()
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->registerRouter(
                $this->createMock('Teknoo\East\Framework\Router\RouterInterface')
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
        $router = $this->createMock('Teknoo\East\Framework\Router\RouterInterface');
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->unregisterRouter(
                $router
            )
        );

        $router = $this->createMock('Teknoo\East\Framework\Router\RouterInterface');
        $this->assertInstanceOf(
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
            ): RouterInterface
            {
                $this->testSuite->assertInstanceOf(
                    $this->testSuite->getManagerClass(),
                    $manager->stopPropagation()
                );

                return $this;
            }
        };


        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->createMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $serverRequestMock
         */
        $serverRequestMock = $this->createMock('Psr\Http\Message\ServerRequestInterface');

        $manager = $this->buildManager();
        $manager->registerRouter($router);
        $manager->receiveRequestFromClient($clientMock,$serverRequestMock);
    }

    public function testBehaviorReceiveRequestFromClient()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->createMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $serverRequestMock
         */
        $serverRequestMock = $this->createMock('Psr\Http\Message\ServerRequestInterface');

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router1 = $this->createMock('Teknoo\East\Framework\Router\RouterInterface');
        $router1->expects($this->once())->method('receiveRequestFromServer')->willReturnSelf();
        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router2 = $this->createMock('Teknoo\East\Framework\Router\RouterInterface');
        $router2->expects($this->once())->method('receiveRequestFromServer');
        $router2->expects($this->once())->method('receiveRequestFromServer')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $this->assertNotSame($managerPassed, $manager);
                $managerPassed->stopPropagation();
            }
        );

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router3 = $this->createMock('Teknoo\East\Framework\Router\RouterInterface');
        $router3->expects($this->never())->method('receiveRequestFromServer');

        $manager->registerRouter($router1);
        $manager->registerRouter($router2);
        $manager->registerRouter($router3);
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequestFromClient($clientMock,$serverRequestMock)
        );
    }
}
