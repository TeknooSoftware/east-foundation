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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\Router;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\FoundationBundle\Router\Router;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Zend\Diactoros\Uri;

/**
 * Class RouterTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Router\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ProcessorInterface
     */
    private $processor;

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
     * @return ProcessorInterface
     */
    private function getProcessorMock(): ProcessorInterface
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->createMock(ProcessorInterface::class);
        }

        return $this->processor;
    }

    /**
     * @return Router
     */
    private function buildRouter(): Router
    {
        return new Router($this->getUrlMatcherMock(), $this->getProcessorMock());
    }

    /**
     * @return string
     */
    private function getRouterClass(): string
    {
        return Router::class;
    }

    public function testReceiveRequestFromServerNotFound()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->expects($this->any())->method('getUri')->willReturn(new class extends Uri{});
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn([]);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerNotFoundException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(new class extends Uri{});
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willThrowException(new ResourceNotFoundException());

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testReceiveRequestFromServerOtherException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(new class extends Uri{});
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willThrowException(new \Exception());

        $this->buildRouter()->receiveRequestFromServer($client, $request, $manager);
    }

    public function testReceiveRequestFromServer()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn(new class extends Uri{});
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects($this->any())->method('match')->willReturn(['foo','bar']);

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorClient()
    {
        $this->buildRouter()->receiveRequestFromServer(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorRequest()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorManager()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }
}
