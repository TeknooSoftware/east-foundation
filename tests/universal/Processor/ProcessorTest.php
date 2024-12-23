<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Processor;

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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Processor::class)]
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Processor
     */
    private function buildProcessor(bool $inSilentMode = false)
    {
        return new Processor($inSilentMode);
    }

    public function testExecuteRequestWithNoResultClientInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $requestMock,
                $manager
            )
        );
    }

    public function testExecuteRequestWithNoResultClientNotInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $requestMock,
                $manager
            )
        );
    }

    public function testExecuteRequestAndPreventionOfVarRequestAndClientVarOverwrittingInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createMock(Request::class)
        ]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects($this->any())->method('getController')->willReturn($controller = function () {
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

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteRequestAndPreventionOfVarRequestAndClientVarOverwrittingNotInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->once())->method('mustSendAResponse');
        $clientMock->expects($this->any())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->any())->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createMock(Request::class)
        ]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects($this->any())->method('getController')->willReturn($controller = function () {
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

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }

    public function testExecuteMessageWithNoResultInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(true)->execute(
                $clientMock,
                $messageMock,
                $manager
            )
        );
    }

    public function testExecuteMessageWithNoResultNotInSilentMode()
    {
        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->expects($this->never())->method('mustSendAResponse');
        $clientMock->expects($this->once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor(false)->execute(
                $clientMock,
                $messageMock,
                $manager
            )
        );
    }
}
