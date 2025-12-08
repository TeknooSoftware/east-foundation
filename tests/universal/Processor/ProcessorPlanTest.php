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

use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\ProcessorPlan;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProcessorPlan::class)]
class ProcessorPlanTest extends TestCase
{
    private ?ProcessorRecipeInterface $recipe = null;

    private ?ProcessorInterface $processor = null;

    public function getRecipeMock(): ProcessorRecipeInterface&MockObject
    {
        if (
            !$this->recipe instanceof ProcessorRecipeInterface
            || !$this->recipe instanceof MockObject
        ) {
            $this->recipe = $this->createMock(ProcessorRecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getRecipeStub(): ProcessorRecipeInterface&Stub
    {
        if (!$this->recipe instanceof ProcessorRecipeInterface) {
            $this->recipe = $this->createStub(ProcessorRecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getProcessorMock(): \Teknoo\East\Foundation\Processor\ProcessorInterface|MockObject
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->createStub(ProcessorInterface::class);
        }

        return $this->processor;
    }

    public function buildPlan(): ProcessorPlan
    {
        return new ProcessorPlan(
            $this->getRecipeMock(),
            $this->getProcessorMock()
        );
    }

    public function buildPlanWithStub(): ProcessorPlan
    {
        return new ProcessorPlan(
            $this->getRecipeStub(),
            $this->getProcessorMock()
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
            ProcessorPlan::class,
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
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        $plan = $this->buildPlan();
        $this->assertInstanceOf(
            ProcessorPlan::class,
            $plan->train($this->createStub(ChefInterface::class))
        );
        $this->assertInstanceOf(
            ProcessorPlan::class,
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
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        $wp = [];
        $this->assertInstanceOf(
            ProcessorPlan::class,
            $this->buildPlan()->prepare($wp, $this->createStub(ChefInterface::class))
        );
    }

    public function testValidate(): void
    {
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        $this->assertInstanceOf(
            ProcessorPlan::class,
            $this->buildPlan()->validate([])
        );
    }
}
