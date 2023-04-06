<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\EndPoint;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
     * @var BowlInterface
     */
    private $bowl;

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

    /**
     * @return BowlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getBowlMock(): BowlInterface
    {
        if (!$this->bowl instanceof BowlInterface) {
            $this->bowl = $this->createMock(BowlInterface::class);
        }

        return $this->bowl;
    }

    public function testConstructorWithBadRecipe()
    {
        $this->expectException(\TypeError::class);
        new RecipeEndPoint(new \stdClass());
    }

    public function testConstructorWithBadContainer()
    {
        $this->expectException(\TypeError::class);
        new RecipeEndPoint($this->createMock(BaseRecipeInterface::class), new \stdClass());
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

        $endPoint = new RecipeEndPoint($this->getRecipeMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $this->createMock(ServerRequestInterface::class))
        );
    }

    public function testInvokeWithRecipeAndSupervisor()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $endPoint = new RecipeEndPoint($this->getRecipeMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithCookBookWithSupervisor()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $endPoint = new RecipeEndPoint($this->getCookbookMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithBowl()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $this->getBowlMock()->expects(self::once())
            ->method('execute')
            ->with($managerMock, [])
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getBowlMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $this->createMock(ServerRequestInterface::class))
        );
    }

    public function testInvokeWithBowlWithSupervisor()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $supervisor = $this->createMock(CookingSupervisorInterface::class);

        $this->getBowlMock()->expects(self::once())
            ->method('execute')
            ->with($managerMock, [], $supervisor)
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getBowlMock());

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createMock(ServerRequestInterface::class),
                $supervisor
            )
        );
    }

    public function testInvokeWithRecipeWithContainer()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturn(new \stdClass());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new \stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getRecipeMock(), $container);

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithCookBookWithContainer()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturn(new \stdClass());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new \stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getCookbookMock(), $container);

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithBowlWithContainer()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $this->getBowlMock()->expects(self::once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new \stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new \stdClass()
                ],
                $supervisor,
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturn(new \stdClass());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new \stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMock(), $container);

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $request, $supervisor)
        );
    }

    public function testInvokeWithBowlWithContainerAndDuplicateKeyIntoWorkPlan()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $this->getBowlMock()->expects(self::once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new \stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new \stdClass()
                ],
                $supervisor
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturn(new \stdClass());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new \stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMock(), $container, ['bar1' => new \stdClass()]);

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $supervisor,
            )
        );
    }

    public function testInvokeWithBowlWithContainerAndInitializedWorkPlan()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $this->getBowlMock()->expects(self::once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new \stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new \stdClass(),
                    'foo' => 'bar'
                ],
                $supervisor
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturn(new \stdClass());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new \stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMock(), $container, ['foo' => 'bar']);

        self::assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $supervisor,
            )
        );
    }

    public function testInvokeWithRecipeWithContainerKeyNotFound()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::any())->method('has')->willReturn(false);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getRecipeMock(), $container);

        $this->expectException(\DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $this->createMock(CookingSupervisorInterface::class),
        );
    }

    public function testInvokeWithCookBookWithContainerKeyNotFound()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::any())->method('has')->willReturn(false);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getCookbookMock(), $container);

        $this->expectException(\DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $this->createMock(CookingSupervisorInterface::class),
        );
    }

    public function testInvokeWithBowlWithContainerKeyNotFound()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $this->getBowlMock()->expects(self::never())
            ->method('execute');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::any())->method('has')->willReturn(false);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMock(), $container);

        $this->expectException(\DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $supervisor,
        );
    }
}
