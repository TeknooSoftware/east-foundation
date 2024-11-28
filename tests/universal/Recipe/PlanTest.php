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

namespace Teknoo\Tests\East\Foundation\Recipe;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorPlanInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\Plan;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Plan::class)]
class PlanTest extends TestCase
{
    private ?RouterInterface $router = null;

    private ?ProcessorPlanInterface $processorPlan = null;

    private ?LoopDetectorInterface $loopDetector = null;

    private ?RecipeInterface $recipe = null;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|RouterInterface|null
     */
    public function getRouterMock()
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ProcessorPlanInterface|null
     */
    public function getProcessorPlanMock()
    {
        if (!$this->processorPlan instanceof ProcessorPlanInterface) {
            $this->processorPlan = $this->createMock(ProcessorPlanInterface::class);
        }

        return $this->processorPlan;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|LoopDetectorInterface|null
     */
    public function getLoopDetectorMock()
    {
        if (!$this->loopDetector instanceof LoopDetectorInterface) {
            $this->loopDetector = $this->createMock(LoopDetectorInterface::class);
        }

        return $this->loopDetector;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|RecipeInterface|null
     */
    public function getRecipeMock()
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function buildPlan(): Plan
    {
        return new Plan(
            $this->getRecipeMock(),
            $this->getRouterMock(),
            $this->getProcessorPlanMock(),
            $this->getLoopDetectorMock()
        );
    }

    public function testFillWithWrongRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildPlan()->fill(new \stdClass());
    }

    public function testFillWithOriginalRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildPlan()->fill($this->createMock(OriginalRecipeInterface::class));
    }

    public function testFill()
    {
        self::assertInstanceOf(
            Plan::class,
            $this->buildPlan()->fill($this->createMock(ProcessorRecipeInterface::class))
        );
    }

    public function testTrainWithWrongChef()
    {
        $this->expectException(\TypeError::class);
        $this->buildPlan()->train(new \stdClass());
    }

    public function testTrain()
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        $plan = $this->buildPlan();
        self::assertInstanceOf(
            Plan::class,
            $plan->train($this->createMock(ChefInterface::class))
        );

        self::assertInstanceOf(
            Plan::class,
            $plan->train($this->createMock(ChefInterface::class))
        );
    }

    public function testPrepareWithWrongWorkplan()
    {
        $this->expectException(\TypeError::class);
        $wp = new \stdClass();
        $this->buildPlan()->prepare($wp, $this->createMock(ChefInterface::class));
    }

    public function testPrepareWithWrongChef()
    {
        $this->expectException(\TypeError::class);
        $wp = [];
        $this->buildPlan()->prepare($wp, new \stdClass());
    }

    public function testPrepare()
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        $wp = [];
        self::assertInstanceOf(
            Plan::class,
            $this->buildPlan()->prepare($wp, $this->createMock(ChefInterface::class))
        );
    }

    public function testValidate()
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        self::assertInstanceOf(
            Plan::class,
            $this->buildPlan()->validate([])
        );
    }
}
