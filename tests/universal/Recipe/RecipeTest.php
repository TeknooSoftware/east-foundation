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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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

/**
 * @covers \Teknoo\East\Foundation\Recipe\Recipe
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeTest extends TestCase
{
    public function testRegisterMiddlewareBadMiddleware()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->registerMiddleware(new \stdClass(), 10, 'foo');
    }

    public function testRegisterMiddlewareBadName()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->registerMiddleware($this->createMock(MiddlewareInterface::class), new \stdClass(), 'foo');
    }

    public function testRegisterMiddlewareBadPriority()
    {
        $this->expectException(\TypeError::class);
        (new Recipe())->registerMiddleware($this->createMock(MiddlewareInterface::class), 10, new \stdClass());
    }

    public function testRegisterMiddleware()
    {
        self::assertInstanceOf(
            Recipe::class,
            (new Recipe())->registerMiddleware($this->createMock(MiddlewareInterface::class), 10)
        );
    }
}
