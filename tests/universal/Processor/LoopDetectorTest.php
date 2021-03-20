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

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;

/**
 * Class LoopDetectorTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Processor\LoopDetector
 */
class LoopDetectorTest extends TestCase
{
    public function buildObject(): LoopDetector
    {
        return new LoopDetector();
    }

    public function testInvokeNoResult()
    {
        $bowl = $this->createMock(RecipeBowl::class);
        $manager = $this->createMock(ManagerInterface::class);

        $bowl->expects(self::once())
            ->method('stopLooping');

        $manager->expects(self::once())
            ->method('updateWorkPlan')
            ->with([ResultInterface::class => null]);

        self::assertInstanceOf(
            LoopDetector::class,
            $this->buildObject()($bowl, $manager)
        );
    }

    public function testInvokeResultNoNext()
    {
        $bowl = $this->createMock(RecipeBowl::class);
        $manager = $this->createMock(ManagerInterface::class);
        $result = $this->createMock(ResultInterface::class);

        $bowl->expects(self::once())
            ->method('stopLooping');

        $manager->expects(self::once())
            ->method('updateWorkPlan')
            ->with([ResultInterface::class => null]);

        self::assertInstanceOf(
            LoopDetector::class,
            $this->buildObject()($bowl, $manager, $result)
        );
    }

    public function testInvokeResultNext()
    {
        $bowl = $this->createMock(RecipeBowl::class);
        $manager = $this->createMock(ManagerInterface::class);
        $result = $this->createMock(ResultInterface::class);
        $next = $this->createMock(ResultInterface::class);
        $result->expects(self::any())
            ->method('getNext')
            ->willReturn($next);

        $bowl->expects(self::never())
            ->method('stopLooping');

        $manager->expects(self::once())
            ->method('updateWorkPlan')
            ->with([ResultInterface::class => $next]);

        self::assertInstanceOf(
            LoopDetector::class,
            $this->buildObject()->__invoke($bowl, $manager, $result)
        );
    }
}
