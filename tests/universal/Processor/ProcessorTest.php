<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Router\ResultInterface;

/**
 * Class ProcessorTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Processor::class)]
class ProcessorTest extends TestCase
{
    private function buildProcessor(bool $inSilentMode = false): \Teknoo\East\Foundation\Processor\Processor
    {
        return new Processor($inSilentMode);
    }

    public function testExecuteRequestWithNoResultClientInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|MockObject
         */
        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $requestMock,
                $manager
            )
        );
    }

    public function testExecuteRequestWithNoResultClientNotInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|MockObject
         */
        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $requestMock,
                $manager
            )
        );
    }

    public function testExecuteRequestAndPreventionOfVarRequestAndClientVarOverwrittingInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|MockObject
         */
        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createStub(Request::class)
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke() {

            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'bar' => 456,
                'foo' => 123,
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestAndPreventionOfVarRequestAndClientVarOverwrittingNotInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|MockObject
         */
        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createStub(Request::class)
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = $controller = new class {
            public function __invoke() {

            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'bar' => 456,
                'foo' => 123,
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestWithAttributesParsedBodyAndQueryParams(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([
            'foo' => 'attribute',
        ]);
        $requestMock->method('getParsedBody')->willReturn([
            'foo' => 'body',
            'bar' => 'body',
            0 => 'body',
        ]);
        $requestMock->method('getQueryParams')->willReturn([
            'foo' => 'query',
            'bar' => 'query',
            'baz' => 'query',
            1 => 'query',
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'foo' => 'attribute',
                'bar' => 'body',
                'baz' => 'query',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestIgnoresFormOptionsFromParsedBodyAndQueryParams(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([]);
        $requestMock->method('getParsedBody')->willReturn([
            'foo' => 'body',
            'formOptions' => ['csrf_protection' => false],
        ]);
        $requestMock->method('getQueryParams')->willReturn([
            'bar' => 'query',
            'formOptions' => ['validation_groups' => false],
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'foo' => 'body',
                'bar' => 'query',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestIgnoresParametersNamedWithANamespaceFromParsedBodyAndQueryParams(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([]);
        $requestMock->method('getParsedBody')->willReturn([
            'foo' => 'body',
            'Foo\\Bar' => 'body',
            '\\Foo' => 'body',
        ]);
        $requestMock->method('getQueryParams')->willReturn([
            'bar' => 'query',
            'Bar\\Foo' => 'query',
            'Psr\\Http\\Message\\UriInterface' => 'query',
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'foo' => 'body',
                'bar' => 'query',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestKeepsFormOptionsAndParametersNamedWithANamespaceFromAttributes(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([
            'formOptions' => ['csrf_protection' => true],
            'Foo\\Bar' => 'attribute',
        ]);
        $requestMock->method('getParsedBody')->willReturn([
            'formOptions' => ['csrf_protection' => false],
            'Foo\\Bar' => 'body',
        ]);
        $requestMock->method('getQueryParams')->willReturn([
            'formOptions' => ['csrf_protection' => false],
            'Foo\\Bar' => 'query',
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'formOptions' => ['csrf_protection' => true],
                'Foo\\Bar' => 'attribute',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestIgnoresAnObjectAsParsedBody(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([]);
        $requestMock->method('getParsedBody')->willReturn(
            (object) [
                'foo' => 'body',
                'formOptions' => ['csrf_protection' => false],
            ]
        );
        $requestMock->method('getQueryParams')->willReturn([
            'bar' => 'query',
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'bar' => 'query',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestWithoutParsedBody(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        $requestMock = $this->createStub(ServerRequestInterface::class);
        $requestMock->method('getAttributes')->willReturn([]);
        $requestMock->method('getParsedBody')->willReturn(null);
        $requestMock->method('getQueryParams')->willReturn([
            'bar' => 'query',
        ]);

        $routerResult = $this->createStub(ResultInterface::class);
        $routerResult->method('getController')->willReturn($controller = new class {
            public function __invoke(): void
            {
            }
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'bar' => 'query',
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'request' => $requestMock,
                ClientInterface::class => $clientMock,
                MessageInterface::class => $requestMock,
                ManagerInterface::class => $manager,
            ]);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteMessageWithNoResultInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createStub(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $messageMock,
                $manager
            )
        );
    }

    public function testExecuteMessageWithNoResultNotInSilentMode(): void
    {
        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createStub(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $messageMock,
                $manager
            )
        );
    }
}
