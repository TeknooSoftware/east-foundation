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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\ProcessorPlan;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProcessorPlan::class)]
class ProcessorPlanTest extends TestCase
{
    private ?ProcessorRecipeInterface $recipe = null;

    private ?ProcessorInterface $processor = null;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ProcessorRecipeInterface|null
     */
    public function getRecipeMock()
    {
        if (!$this->recipe instanceof ProcessorRecipeInterface) {
            $this->recipe = $this->createMock(ProcessorRecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ProcessorInterface|null
     */
    public function getProcessorMock()
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->createMock(ProcessorInterface::class);
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
            ProcessorPlan::class,
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
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        $plan = $this->buildPlan();
        self::assertInstanceOf(
            ProcessorPlan::class,
            $plan->train($this->createMock(ChefInterface::class))
        );
        self::assertInstanceOf(
            ProcessorPlan::class,
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
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        $wp = [];
        self::assertInstanceOf(
            ProcessorPlan::class,
            $this->buildPlan()->prepare($wp, $this->createMock(ChefInterface::class))
        );
    }

    public function testValidate()
    {
        $this->getRecipeMock()->expects($this->exactly(2))->method('cook')->willReturnSelf();

        self::assertInstanceOf(
            ProcessorPlan::class,
            $this->buildPlan()->validate([])
        );
    }
}
