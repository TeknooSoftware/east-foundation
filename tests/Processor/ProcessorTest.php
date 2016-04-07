<?php

namespace Teknoo\Tests\East\Framework\Processor;

use Psr\Http\Message\ServerRequestInterface;
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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([]);

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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([]);

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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([]);

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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([]);

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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 123]);

        $callableController = new class
        {
            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar)
            {
            }
        };

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => get_class($callableController)
            ]
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteRequestBadArgumentWithClassController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 123]);

        $callableController = new class
        {
            public function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar)
            {
            }
        };

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => get_class($callableController).'::testAction'
            ]
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteRequestBadArgumentWithFunction()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([]);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => 'microtime'
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
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 456,'foo' => 123]);

        $callableController = new class
        {
            /**
             * @var ClientInterface
             */
            private static $client;

            /**
             * @var ServerRequestInterface
             */
            private static $request;

            /**
             * @var ProcessorTest
             */
            private static $testCase;

            public function setValue(ClientInterface $client, ServerRequestInterface $request, ProcessorTest $testCase)
            {
                static::$client = $client;
                static::$request = $request;
                static::$testCase = $testCase;

                return $this;
            }

            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default=789)
            {
                static::$testCase->assertEquals(static::$request, $request);
                static::$testCase->assertEquals(static::$client, $client);
                static::$testCase->assertEquals(123, $foo);
                static::$testCase->assertEquals(456, $bar);
                static::$testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => get_class($callableController)
            ]
        );
    }

    public function testExecuteRequestCallableConstructor()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 456,'foo' => 123]);

        $callableController = new class
        {
            /**
             * @var ClientInterface
             */
            private static $client;

            /**
             * @var ServerRequestInterface
             */
            private static $request;

            /**
             * @var ProcessorTest
             */
            private static $testCase;

            public function setValue(ClientInterface $client, ServerRequestInterface $request, ProcessorTest $testCase)
            {
                static::$client = $client;
                static::$request = $request;
                static::$testCase = $testCase;

                return $this;
            }

            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default=789)
            {
                static::$testCase->assertEquals(static::$request, $request);
                static::$testCase->assertEquals(static::$client, $client);
                static::$testCase->assertEquals(123, $foo);
                static::$testCase->assertEquals(456, $bar);
                static::$testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => $callableController
            ]
        );
    }

    public function testExecuteRequestClassConstructor()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 456,'foo' => 123]);

        $callableController = new class
        {
            /**
             * @var ClientInterface
             */
            private static $client;

            /**
             * @var ServerRequestInterface
             */
            private static $request;

            /**
             * @var ProcessorTest
             */
            private static $testCase;

            public function setValue(ClientInterface $client, ServerRequestInterface $request, ProcessorTest $testCase)
            {
                static::$client = $client;
                static::$request = $request;
                static::$testCase = $testCase;

                return $this;
            }

            public function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default=789)
            {
                static::$testCase->assertEquals(static::$request, $request);
                static::$testCase->assertEquals(static::$client, $client);
                static::$testCase->assertEquals(123, $foo);
                static::$testCase->assertEquals(456, $bar);
                static::$testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => get_class($callableController).'::testAction'
            ]
        );
    }

    public function testExecuteRequestControllerHasFunction()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject $clientMock
         */
        $clientMock = $this->getMock('Teknoo\East\Framework\Http\ClientInterface');

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock
         */
        $requestMock = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['get_as_float' => true]);

        $this->buildProcessor()->executeRequest(
            $clientMock,
            $requestMock, [
                '_controller' => 'microtime'
            ]
        );
    }
}