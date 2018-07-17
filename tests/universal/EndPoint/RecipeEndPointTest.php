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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
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
use Teknoo\Recipe\RecipeInterface;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
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
     * @return RecipeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRecipeMock(): RecipeInterface
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return RecipeEndPoint
     */
    public function buildEndPoint(): RecipeEndPoint
    {
        return new RecipeEndPoint($this->getRecipeMock());
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvokeBadManager()
    {
        $endPoint = $this->buildEndPoint();
        $endPoint($this->createMock(ServerRequestInterface::class), new \stdClass());
    }

    public function testInvoke()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $managerMock->expects(self::once())
            ->method('setAsideAndBegin')
            ->with($this->getRecipeMock())
            ->willReturnSelf();

        $managerMock->expects(self::once())
            ->method('process')
            ->with([])
            ->willReturnSelf();

        $endPoint = $this->buildEndPoint();

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock)
        );
    }
}
