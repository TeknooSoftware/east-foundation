<?php

namespace Teknoo\Tests\East\Framework\Router;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Teknoo\East\Framework\Router\Router;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Framework\Processor\ProcessorInterface;

/**
 * Class RouterTest
 * @package Teknoo\Tests\East\Framework\Router
 * @covers Teknoo\East\Framework\Router\Router
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
            $this->matcher = $this->getMock(
                'Symfony\Component\Routing\Matcher\UrlMatcherInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->matcher;
    }

    /**
     * @return ProcessorInterface
     */
    private function getProcessorMock(): ProcessorInterface
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->getMock(
                'Teknoo\East\Framework\Processor\ProcessorInterface',
                [],
                [],
                '',
                false
            );
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
        return 'Teknoo\East\Framework\Router\Router';
    }

    public function testReceiveRequestFromServerNotFound()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->getMock('Teknoo\East\Framework\Manager\ManagerInterface');
        $manager->expects($this->never())->method('stopPropagation');

        $this->assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServer()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $client $client
         */
        $client = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $client $request
         */
        $request = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $client $manager
         */
        $manager = $this->getMock('Teknoo\East\Framework\Manager\ManagerInterface');
        $manager->expects($this->once())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects($this->any())->method('matchRequest')->willReturn(['foo','bar']);

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
            $this->getMock('Psr\Http\Message\ServerRequestInterface'),
            $this->getMock('Teknoo\East\Framework\Manager\ManagerInterface')
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorRequest()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
            new \stdClass(),
            $this->getMock('Teknoo\East\Framework\Manager\ManagerInterface')
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorManager()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
            $this->getMock('Psr\Http\Message\ServerRequestInterface'),
            new \stdClass()
        );
    }
}