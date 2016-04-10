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

namespace Teknoo\Tests\East\Framework\Manager;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\Manager;
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
        $router = $this->getMock('Teknoo\East\Framework\Router\RouterInterface');
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildManager()->unregisterRouter(
                $router
            )
        );

        $router = $this->getMock('Teknoo\East\Framework\Router\RouterInterface');
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
                $managerPassed->stopPropagation();
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