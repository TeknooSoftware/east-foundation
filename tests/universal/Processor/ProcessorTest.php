<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\Processor;

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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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
        $clientMock->expects(self::never())->method('mustSendAResponse');
        $clientMock->expects(self::once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $clientMock->expects(self::never())->method('mustSendAResponse');
        $clientMock->expects(self::once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn(['bar' => 456, 'foo' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $clientMock->expects(self::never())->method('mustSendAResponse');
        $clientMock->expects(self::once())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createMock(Request::class)
        ]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($controller = function () {
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
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
        $clientMock->expects(self::once())->method('mustSendAResponse');
        $clientMock->expects(self::any())->method('sendAResponseIsOptional');

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createMock(Request::class)
        ]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($controller = function () {
        });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
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
        $clientMock->expects(self::never())->method('mustSendAResponse');
        $clientMock->expects(self::once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $clientMock->expects(self::never())->method('mustSendAResponse');
        $clientMock->expects(self::once())->method('sendAResponseIsOptional');

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');

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
