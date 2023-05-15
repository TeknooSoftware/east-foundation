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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Processor;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;

/**
 * Class LoopDetectorTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
