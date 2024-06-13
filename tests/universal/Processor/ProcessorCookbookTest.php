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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\ProcessorCookbook;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProcessorCookbook::class)]
class ProcessorCookbookTest extends TestCase
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

    public function buildCookbook(): ProcessorCookbook
    {
        return new ProcessorCookbook(
            $this->getRecipeMock(),
            $this->getProcessorMock()
        );
    }

    public function testFillWithWrongRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildCookbook()->fill(new \stdClass());
    }

    public function testFillWithOriginalRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildCookbook()->fill($this->createMock(OriginalRecipeInterface::class));
    }

    public function testFill()
    {
        self::assertInstanceOf(
            ProcessorCookbook::class,
            $this->buildCookbook()->fill($this->createMock(ProcessorRecipeInterface::class))
        );
    }

    public function testTrainWithWrongChef()
    {
        $this->expectException(\TypeError::class);
        $this->buildCookbook()->train(new \stdClass());
    }

    public function testTrain()
    {
        $this->getRecipeMock()->expects($this->once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();

        $cookbook = $this->buildCookbook();
        self::assertInstanceOf(
            ProcessorCookbook::class,
            $cookbook->train($this->createMock(ChefInterface::class))
        );
        self::assertInstanceOf(
            ProcessorCookbook::class,
            $cookbook->train($this->createMock(ChefInterface::class))
        );
    }

    public function testPrepareWithWrongWorkplan()
    {
        $this->expectException(\TypeError::class);
        $wp = new \stdClass();
        $this->buildCookbook()->prepare($wp, $this->createMock(ChefInterface::class));
    }

    public function testPrepareWithWrongChef()
    {
        $this->expectException(\TypeError::class);
        $wp = [];
        $this->buildCookbook()->prepare($wp, new \stdClass());
    }

    public function testPrepare()
    {
        $this->getRecipeMock()->expects($this->once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();

        $wp = [];
        self::assertInstanceOf(
            ProcessorCookbook::class,
            $this->buildCookbook()->prepare($wp, $this->createMock(ChefInterface::class))
        );
    }

    public function testValidate()
    {
        $this->getRecipeMock()->expects($this->once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects($this->once())->method('cook')->willReturnSelf();

        self::assertInstanceOf(
            ProcessorCookbook::class,
            $this->buildCookbook()->validate([])
        );
    }
}
