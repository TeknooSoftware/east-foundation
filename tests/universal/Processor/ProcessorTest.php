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

namespace Teknoo\Tests\East\Foundation\Processor;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Router\Parameter;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\FoundationBundle\Http\Client;

/**
 * Class ProcessorTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Processor\Processor
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Processor
     */
    private function buildProcessor()
    {
        return new Processor();
    }

    public function testExecuteNoResult()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, null]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    public function testExecute()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $callableController = function (
            ServerRequestInterface $r,
            ClientInterface $c,
            $foo,
            $bar,
            $default = 789
        ) use ($requestMock, $clientMock) {
            self::assertEquals($requestMock, $r);
            self::assertEquals($clientMock, $c);
            self::assertEquals(123, $foo);
            self::assertEquals(456, $bar);
            self::assertEquals(789, $default);
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    public function testExecuteWithSymfonyClient()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(Client::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $callableController = function (
            ServerRequestInterface $r,
            ClientInterface $c,
            $foo,
            $bar,
            $default = 789
        ) use ($requestMock, $clientMock) {
            self::assertEquals($requestMock, $r);
            self::assertEquals($clientMock, $c);
            self::assertEquals(123, $foo);
            self::assertEquals(456, $bar);
            self::assertEquals(789, $default);
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    public function testExecuteChaining()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $output = null;

        $callableController1 = function (
            ServerRequestInterface $r,
            ClientInterface $c,
            $foo,
            $bar,
            $default = 789
        ) use ($requestMock, $clientMock, &$output) {
            self::assertEquals($requestMock, $r);
            self::assertEquals($clientMock, $c);
            self::assertEquals(123, $foo);
            self::assertEquals(789, $default);
            self::assertEmpty($output);
            $output = 'result1';
        };

        $callableController2 = function (
            ServerRequestInterface $r,
            ClientInterface $c,
            $foo,
            $bar,
            $default = 111
        ) use ($requestMock, $clientMock, &$output) {
            self::assertEquals($requestMock, $r);
            self::assertEquals($clientMock, $c);
            self::assertEquals(123, $foo);
            self::assertEquals(456, $bar);
            self::assertEquals(111, $default);
            self::assertEquals('result1', $output);
            $output = 'result2';
        };

        $callableController3 = function (
            ClientInterface $c
        ) use ($clientMock, &$output) {
            self::assertEquals($clientMock, $c);
            self::assertEquals('result2', $output);
            $output = 'result3';
        };

        $routerResult3 = $this->createMock(ResultInterface::class);
        $routerResult3->expects(self::any())->method('getController')->willReturn($callableController3);
        $routerResult3->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
        ]);
        $routerResult3->expects(self::any())->method('getNext')->willReturn(null);

        $routerResult2 = $this->createMock(ResultInterface::class);
        $routerResult2->expects(self::any())->method('getController')->willReturn($callableController2);
        $routerResult2->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 111, null),
        ]);
        $routerResult2->expects(self::any())->method('getNext')->willReturn($routerResult3);

        $routerResult1 = $this->createMock(ResultInterface::class);
        $routerResult1->expects(self::any())->method('getController')->willReturn($callableController1);
        $routerResult1->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult1->expects(self::any())->method('getNext')->willReturn($routerResult2);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult1]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));

        self::assertEquals('result3', $output);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteMissingArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);

        $callableController = function (
            ServerRequestInterface $r,
            ClientInterface $c,
            $foo,
            $bar,
            $default = 789
        ) {
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteInvokableConstructor()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);
        $requestMock->expects(self::any())->method('getParsedBody')->willReturn(['foo' => 123]);
        $requestMock->expects(self::any())->method('getQueryParams')->willReturn(['bar' => 123]);

        $callableController = new class() {
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

            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
                $this->testCase->assertEquals($this->request, $request);
                $this->testCase->assertEquals($this->client, $client);
                $this->testCase->assertEquals(123, $foo);
                $this->testCase->assertEquals(456, $bar);
                $this->testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    public function testExecuteParserBodyAndQueryParamNotOverloadAttribute()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['foo' => 123, 'bar' => 456]);
        $requestMock->expects(self::any())->method('getParsedBody')->willReturn(['foo' => 999]);
        $requestMock->expects(self::any())->method('getQueryParams')->willReturn(['bar' => 888]);

        $callableController = new class() {
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

            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
                $this->testCase->assertEquals($this->request, $request);
                $this->testCase->assertEquals($this->client, $client);
                $this->testCase->assertEquals(123, $foo);
                $this->testCase->assertEquals(456, $bar);
                $this->testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteInvokableConstructorMissingArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);

        $callableController = new class() {
            public function __invoke(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
            }
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($callableController);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteClassController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $callableController = new class() {
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

            public function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
                $this->testCase->assertEquals($this->request, $request);
                $this->testCase->assertEquals($this->client, $client);
                $this->testCase->assertEquals(123, $foo);
                $this->testCase->assertEquals(456, $bar);
                $this->testCase->assertEquals(789, $default);
            }
        };

        $callableController->setValue($clientMock, $requestMock, $this);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn([$callableController, 'testAction']);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteClassControllerMissingArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);

        $callableController = new class() {
            public function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
            }
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn([$callableController, 'testAction']);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteClassStaticController()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $callableController = new class() {
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

            public static function setValue(ClientInterface $client, ServerRequestInterface $request, ProcessorTest $testCase)
            {
                self::$client = $client;
                self::$request = $request;
                self::$testCase = $testCase;
            }

            public static function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
                self::$testCase->assertEquals(self::$request, $request);
                self::$testCase->assertEquals(self::$client, $client);
                self::$testCase->assertEquals(123, $foo);
                self::$testCase->assertEquals(456, $bar);
                self::$testCase->assertEquals(789, $default);
            }
        };

        $setValueCallBack = [\get_class($callableController), 'setValue'];
        $setValueCallBack($clientMock, $requestMock, $this);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn([\get_class($callableController), 'testAction']);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteClassStaticControllerMissingArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456]);

        $callableController = new class() {
            public static function testAction(ServerRequestInterface $request, ClientInterface $client, $foo, $bar, $default = 789)
            {
            }
        };

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn([\get_class($callableController), 'testAction']);
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('r', false, null, new \ReflectionClass(ServerRequestInterface::class)),
            new Parameter('c', false, null, new \ReflectionClass(ClientInterface::class)),
            new Parameter('foo', false, null, null),
            new Parameter('bar', false, null, null),
            new Parameter('default', true, 789, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteControllerHasFunction()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['get_as_float' => true]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn('microtime');
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('get_as_float', true, false, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        self::assertInstanceOf(ProcessorInterface::class, $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteControllerHasFunctionMissingArgument()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn([]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn('explode');
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('get_as_float', false, false, null),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteControllerHasFunctionBadArgumentType()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn([new \stdClass()]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn('explode');
        $routerResult->expects(self::any())->method('getParameters')->willReturn([
            new Parameter('get_as_float', false, false, new \ReflectionClass(\DateTime::class)),
        ]);
        $routerResult->expects(self::any())->method('getNext')->willReturn(null);
        $requestMock->expects(self::any())->method('getAttribute')->willReturnMap(
            [[RouterInterface::ROUTER_RESULT_KEY, null, $routerResult]]
        );

        $this->buildProcessor()->execute(
            $clientMock,
            $requestMock,
            $this->createMock(ManagerInterface::class)
        );
    }
}
