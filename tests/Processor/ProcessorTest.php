<?php

namespace Teknoo\Tests\East\Framework\Processor;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\East\Framework\Processor\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProcessorTest
 * @package Teknoo\Tests\East\Framework\Processor
 * @covers Teknoo\East\Framework\Processor\Processor
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerMock()
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->getMock(
                'Symfony\Component\DependencyInjection\ContainerInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->container;
    }

    /**
     * @return Processor
     */
    private function buildProcessor()
    {
        return new Processor($this->getContainerMock());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteRequestErrorClient()
    {
        $this->buildProcessor()->executeRequest(
            new \stdClass(),
            $this->getMock('Psr\Http\Message\ServerRequestInterface'),
            []
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteRequestErrorRequest()
    {
        $this->buildProcessor()->executeRequest(
            $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
            new \stdClass(),
            []
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExecuteRequestErrorParameters()
    {
        $this->buildProcessor()->executeRequest(
            $this->getMock('Teknoo\East\Framework\Http\ClientInterface'),
            $this->getMock('Psr\Http\Message\ServerRequestInterface'),
            new \stdClass()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRequestExceptionNoController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock,
            []
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRequestExceptionBadController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock,
            ['_controller' => 'fooBar']
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRequestExceptionInexistantController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock,
            ['_controller' => 'foo::bar']
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRequestExceptionNoCallableController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $this->getContainerMock()->expects($this->any())->method('has')->willReturn(true);
        $this->getContainerMock()->expects($this->any())->method('get')->willReturn('fooBar');

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock,
            ['_controller' => 'foo::bar']
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteRequestBadArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $callableController = new class
        {
            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar)
            {
            }
        };

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => $callableController,
                'bar' => 123
            ]
        );
    }

    public function testExecuteRequest()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');

        $callableController = new class
        {
            /**
             * @var ClientInterface
             */
            private $client;

            /**
             * @var ServerRequestInterface
             */
            private $request;

            /**
             * @var ProcessorTest
             */
            private $testCase;

            public function setValue(ClientInterface $client, ServerRequestInterface $request, ProcessorTest $testCase)
            {
                $this->client = $client;
                $this->request = $request;
                $this->testCase = $testCase;

                return $this;
            }

            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default=789)
            {
                $this->testCase->assertEquals($this->request, $request);
                $this->testCase->assertEquals($this->client, $client);
                $this->testCase->assertEquals(123, $foo);
                $this->testCase->assertEquals(456, $bar);
                $this->testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => $callableController,
                'bar' => 123,
                'foo' => 456
            ]
        );
    }
}