<?php

namespace Teknoo\Tests\East\Framework\Manager;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\Manager;
use Teknoo\East\Framework\Router\RouterInterface;

/**
 * Class ManagerTest
 * @package Teknoo\Tests\East\Framework\Manager
 * @covers Teknoo\East\Framework\Manager\Manager
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
    private function getManagerClass(): string
    {
        return 'Teknoo\East\Framework\Manager\Manager';
    }

    public function testReceiveRequestFromClient()
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->receiveRequestFromClient(
                $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
                $this->getMock('Psr\Http\Message\ServerRequestInterface')
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
            $this->getMock('Psr\Http\Message\ServerRequestInterface')
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromClientErrorRequest()
    {
        $this->buildManager()->receiveRequestFromClient(
            $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
            new \stdClass()
        );
    }

    public function testRegisterRouter()
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->registerRouter(
                $this->getMock('Teknoo\East\Framework\Router\RouterInterface')
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
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->unregisterRouter(
                $this->getMock('Teknoo\East\Framework\Router\RouterInterface')
            )
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
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->stopPropagation()
        );
    }

    public function testBehaviorReceiveRequestFromClient()
    {
        $manager = $this->buildManager();

        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $serverRequestMock
         */
        $serverRequestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router1 = $this->getMock('Teknoo\East\Framework\Router\RouterInterface');
        $router1->expects($this->once())->method('receiveRequestFromServer')->willReturnSelf();
        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router2 = $this->getMock('Teknoo\East\Framework\Router\RouterInterface');
        $router2->expects($this->once())->method('receiveRequestFromServer');
        $router2->expects($this->once())->method('receiveRequestFromServer')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $this->assertNotSame($managerPassed, $manager);
                $manager->stopPropagation();
            }
        );

        /**
         * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router1
         */
        $router3 = $this->getMock('Teknoo\East\Framework\Router\RouterInterface');
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