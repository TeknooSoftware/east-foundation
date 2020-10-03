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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\Recipe;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorCookbookInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\RecipeCookbook;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * @covers \Teknoo\East\Foundation\Recipe\RecipeCookbook
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeCookbookTest extends TestCase
{
    private ?RouterInterface $router = null;

    private ?ProcessorCookbookInterface $processorCookbook = null;

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
     * @return \PHPUnit\Framework\MockObject\MockObject|ProcessorCookbookInterface|null
     */
    public function getProcessorCookbookMock()
    {
        if (!$this->processorCookbook instanceof ProcessorCookbookInterface) {
            $this->processorCookbook = $this->createMock(ProcessorCookbookInterface::class);
        }

        return $this->processorCookbook;
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

    public function buildCookbook(): RecipeCookbook
    {
        return new RecipeCookbook(
            $this->getRecipeMock(),
            $this->getRouterMock(),
            $this->getProcessorCookbookMock(),
            $this->getLoopDetectorMock()
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
            RecipeCookbook::class,
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
        $this->getRecipeMock()->expects(self::any())->method('registerMiddleware')->willReturnSelf();
        $this->getRecipeMock()->expects(self::any())->method('execute')->willReturnSelf();

        self::assertInstanceOf(
            RecipeCookbook::class,
            $this->buildCookbook()->train($this->createMock(ChefInterface::class))
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
        $wp = [];
        self::assertInstanceOf(
            RecipeCookbook::class,
            $this->buildCookbook()->prepare($wp, $this->createMock(ChefInterface::class))
        );
    }

    public function testValidate()
    {
        self::assertInstanceOf(
            RecipeCookbook::class,
            $this->buildCookbook()->validate([])
        );
    }
}
