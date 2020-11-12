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

namespace Teknoo\Tests\East\Foundation\EndPoint;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\EndPoint\RecipeEndPoint
 */
class RecipeEndPointTest extends TestCase
{
    /**
     * @var RecipeInterface
     */
    private $recipe;

    /**
     * @var CookbookInterface
     */
    private $cookbook;

    /**
     * @return RecipeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRecipeMock(): RecipeInterface
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return CookbookInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getCookbookMock(): CookbookInterface
    {
        if (!$this->cookbook instanceof CookbookInterface) {
            $this->cookbook = $this->createMock(CookbookInterface::class);
        }

        return $this->cookbook;
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);
        $endPoint = new RecipeEndPoint($this->getRecipeMock());
        $endPoint($this->createMock(ServerRequestInterface::class), new \stdClass());
    }

    public function testInvokeWithRecipe()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $managerMock->expects(self::once())
            ->method('reserveAndBegin')
            ->with($this->getRecipeMock())
            ->willReturnSelf();

        $managerMock->expects(self::once())
            ->method('process')
            ->with([])
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getRecipeMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock)
        );
    }

    public function testInvokeWithCookBook()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $managerMock->expects(self::once())
            ->method('reserveAndBegin')
            ->with($this->getCookbookMock())
            ->willReturnSelf();

        $managerMock->expects(self::once())
            ->method('process')
            ->with([])
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getCookbookMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock)
        );
    }

    public function testConstructorWithBadArgument()
    {
        $this->expectException(\TypeError::class);
        new RecipeEndPoint(new \stdClass());
    }
}
