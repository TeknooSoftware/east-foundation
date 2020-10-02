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
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @covers \Teknoo\East\Foundation\Recipe\Recipe
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeTest extends TestCase
{
    public function testFillWithBadRecipe()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->fill(new \stdClass());
    }

    public function testFillWithRecipe()
    {
        self::assertInstanceOf(
            Recipe::class,
            (new Recipe())->fill($this->createMock(RecipeInterface::class))
        );
    }

    public function testRegisterMiddlewareBadMiddleware()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->fill(new \stdClass(), 10, 'foo');
    }

    public function testRegisterMiddlewareBadName()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->fill($this->createMock(MiddlewareInterface::class), new \stdClass(), 'foo');
    }

    public function testRegisterMiddlewareBadPriority()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->fill($this->createMock(MiddlewareInterface::class), 10, new \stdClass());
    }

    public function testRegisterMiddleware()
    {
        self::assertInstanceOf(
            Recipe::class,
            (new Recipe())->registerMiddleware($this->createMock(MiddlewareInterface::class), 10)
        );
    }
    
    public function testTrainWithBadChef()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->train(new \stdClass());
    }

    public function testTrainWithChefWithoutRecipe()
    {
        self::assertInstanceOf(
            Recipe::class,
            (new Recipe())->train($this->createMock(ChefInterface::class))
        );
    }

    public function testTrainWithChefWithRecipe()
    {
        self::assertInstanceOf(
            Recipe::class,
            (new Recipe())->fill($this->createMock(RecipeInterface::class))
                ->train($this->createMock(ChefInterface::class))
        );
    }
}
