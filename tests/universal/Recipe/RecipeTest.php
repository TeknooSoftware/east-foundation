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

namespace Teknoo\Tests\East\Foundation\Recipe;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Recipe\Recipe;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Recipe::class)]
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
