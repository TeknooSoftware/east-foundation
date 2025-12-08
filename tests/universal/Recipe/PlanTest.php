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

namespace Teknoo\Tests\East\Foundation\Recipe;

use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Plan::class)]
class PlanTest extends TestCase
{
    private ?RouterInterface $router = null;

    private ?ProcessorPlanInterface $processorPlan = null;

    private ?LoopDetectorInterface $loopDetector = null;

    private ?RecipeInterface $recipe = null;

    public function getRouterMock(): RouterInterface|MockObject
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createStub(RouterInterface::class);
        }

        return $this->router;
    }

    public function getProcessorPlanMock(): ProcessorPlanInterface|MockObject
    {
        if (!$this->processorPlan instanceof ProcessorPlanInterface) {
            $this->processorPlan = $this->createStub(ProcessorPlanInterface::class);
        }

        return $this->processorPlan;
    }

    public function getLoopDetectorMock(): LoopDetectorInterface|MockObject
    {
        if (!$this->loopDetector instanceof LoopDetectorInterface) {
            $this->loopDetector = $this->createStub(LoopDetectorInterface::class);
        }

        return $this->loopDetector;
    }

    public function getRecipeMock(): RecipeInterface&MockObject
    {
        if (
            !$this->recipe instanceof RecipeInterface
            || !$this->recipe instanceof MockObject
        ) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getRecipeStub(): RecipeInterface&Stub
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createStub(RecipeInterface::class);
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

    public function buildPlanWithStub(): Plan
    {
        return new Plan(
            $this->getRecipeStub(),
            $this->getRouterMock(),
            $this->getProcessorPlanMock(),
            $this->getLoopDetectorMock()
        );
    }

    public function testFillWithWrongRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlanWithStub()->fill(new stdClass());
    }

    public function testFillWithOriginalRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlanWithStub()->fill($this->createStub(OriginalRecipeInterface::class));
    }

    public function testFill(): void
    {
        $this->assertInstanceOf(
            Plan::class,
            $this->buildPlanWithStub()->fill($this->createStub(ProcessorRecipeInterface::class))
        );
    }

    public function testTrainWithWrongChef(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlanWithStub()->train(new stdClass());
    }

    public function testTrain(): void
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        $plan = $this->buildPlan();
        $this->assertInstanceOf(
            Plan::class,
            $plan->train($this->createStub(ChefInterface::class))
        );

        $this->assertInstanceOf(
            Plan::class,
            $plan->train($this->createStub(ChefInterface::class))
        );
    }

    public function testPrepareWithWrongWorkplan(): void
    {
        $this->expectException(TypeError::class);
        $wp = new stdClass();
        $this->buildPlanWithStub()->prepare($wp, $this->createStub(ChefInterface::class));
    }

    public function testPrepareWithWrongChef(): void
    {
        $this->expectException(TypeError::class);
        $wp = [];
        $this->buildPlanWithStub()->prepare($wp, new stdClass());
    }

    public function testPrepare(): void
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        $wp = [];
        $this->assertInstanceOf(
            Plan::class,
            $this->buildPlan()->prepare($wp, $this->createStub(ChefInterface::class))
        );
    }

    public function testValidate(): void
    {
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('execute')->willReturnSelf();

        $this->assertInstanceOf(
            Plan::class,
            $this->buildPlan()->validate([])
        );
    }
}
