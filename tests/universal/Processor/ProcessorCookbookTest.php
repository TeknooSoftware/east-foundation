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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Processor;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\ProcessorCookbook;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Foundation\Processor\ProcessorCookbook
 */
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
        $this->getRecipeMock()->expects(self::once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects(self::once())->method('cook')->willReturnSelf();

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
        $this->getRecipeMock()->expects(self::once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects(self::once())->method('cook')->willReturnSelf();

        $wp = [];
        self::assertInstanceOf(
            ProcessorCookbook::class,
            $this->buildCookbook()->prepare($wp, $this->createMock(ChefInterface::class))
        );
    }

    public function testValidate()
    {
        $this->getRecipeMock()->expects(self::once())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects(self::once())->method('cook')->willReturnSelf();

        self::assertInstanceOf(
            ProcessorCookbook::class,
            $this->buildCookbook()->validate([])
        );
    }
}
