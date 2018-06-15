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
use Symfony\Component\HttpFoundation\Request;
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

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');

        self::assertInstanceOf(
            ProcessorInterface::class,
            $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager
            )
        );
    }

    public function testExecuteAndPreventionOfVarRequestAndClientVarOverwritting()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects(self::any())->method('getAttributes')->willReturn([
            'bar' => 456,
            'foo' => 123,
            'request' => $this->createMock(Request::class)
        ]);

        $routerResult = $this->createMock(ResultInterface::class);
        $routerResult->expects(self::any())->method('getController')->willReturn($controller = function() {});

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('updateWorkPlan')
            ->with([
                'bar' => 456,
                'foo' => 123,
                ProcessorInterface::WORK_PLAN_CONTROLLER_KEY => $controller,
                'client' => $clientMock,
                'request' => $requestMock,
                'manager' => $manager,
            ]);

        self::assertInstanceOf(
            ProcessorInterface::class, $this->buildProcessor()->execute(
                $clientMock,
                $requestMock,
                $manager,
                $routerResult
            )
        );
    }
}
