<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\EndPoint;

use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RecipeEndPoint::class)]
class RecipeEndPointTest extends TestCase
{
    private ?RecipeInterface $recipe = null;

    private ?PlanInterface $plan = null;

    private ?BowlInterface $bowl = null;

    private function getRecipeMock(): RecipeInterface&Stub
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    private function getPlanMock(): PlanInterface&Stub
    {
        if (!$this->plan instanceof PlanInterface) {
            $this->plan = $this->createStub(PlanInterface::class);
        }

        return $this->plan;
    }

    private function getBowlMock(): BowlInterface&Stub
    {
        if (!$this->bowl instanceof BowlInterface) {
            $this->bowl = $this->createStub(BowlInterface::class);
        }

        return $this->bowl;
    }

    private function getBowlMockObject(): BowlInterface&MockObject
    {
        if (!$this->bowl instanceof BowlInterface) {
            $this->bowl = $this->createMock(BowlInterface::class);
        }

        return $this->bowl;
    }

    public function testConstructorWithBadRecipe()
    {
        $this->expectException(TypeError::class);
        new RecipeEndPoint(new stdClass());
    }

    public function testConstructorWithBadContainer(): void
    {
        $this->expectException(TypeError::class);
        new RecipeEndPoint($this->createStub(BaseRecipeInterface::class), new stdClass());
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(TypeError::class);
        $endPoint = new RecipeEndPoint($this->getRecipeMock());
        $endPoint($this->createStub(ServerRequestInterface::class), new stdClass());
    }

    public function testInvokeWithRecipe(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $endPoint = new RecipeEndPoint($this->getRecipeMock());

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $this->createStub(ServerRequestInterface::class))
        );
    }

    public function testInvokeWithRecipeAndSupervisor(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $endPoint = new RecipeEndPoint($this->getRecipeMock());

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createStub(ServerRequestInterface::class),
                $this->createStub(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithPlanWithSupervisor(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $endPoint = new RecipeEndPoint($this->getPlanMock());

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createStub(ServerRequestInterface::class),
                $this->createStub(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithBowl(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $this->getBowlMockObject()->expects($this->once())
            ->method('execute')
            ->with($managerMock, [])
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getBowlMockObject());

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $this->createStub(ServerRequestInterface::class))
        );
    }

    public function testInvokeWithBowlWithSupervisor(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $supervisor = $this->createStub(CookingSupervisorInterface::class);

        $this->getBowlMockObject()->expects($this->once())
            ->method('execute')
            ->with($managerMock, [], $supervisor)
            ->willReturnSelf();

        $endPoint = new RecipeEndPoint($this->getBowlMockObject());

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $this->createStub(ServerRequestInterface::class),
                $supervisor
            )
        );
    }

    public function testInvokeWithRecipeWithContainer(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturn(new stdClass());

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getRecipeMock(), $container);

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $this->createStub(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithPlanWithContainer(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturn(new stdClass());

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getPlanMock(), $container);

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $this->createStub(CookingSupervisorInterface::class),
            )
        );
    }

    public function testInvokeWithBowlWithContainer(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $supervisor = $this->createStub(CookingSupervisorInterface::class);
        $this->getBowlMockObject()->expects($this->once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new stdClass()
                ],
                $supervisor,
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturn(new stdClass());

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMockObject(), $container);

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint($managerMock, $request, $supervisor)
        );
    }

    public function testInvokeWithBowlWithContainerAndDuplicateKeyIntoWorkPlan(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $supervisor = $this->createStub(CookingSupervisorInterface::class);
        $this->getBowlMockObject()->expects($this->once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new stdClass()
                ],
                $supervisor
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturn(new stdClass());

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->bowl, $container, ['bar1' => new stdClass()]);

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $supervisor,
            )
        );
    }

    public function testInvokeWithBowlWithContainerAndInitializedWorkPlan(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $supervisor = $this->createStub(CookingSupervisorInterface::class);
        $this->getBowlMockObject()->expects($this->once())
            ->method('execute')
            ->with(
                $managerMock,
                [
                    'bar1' => new stdClass(),
                    'bar2' => '@bar',
                    'foo3' => new stdClass(),
                    'foo' => 'bar'
                ],
                $supervisor
            )
            ->willReturnSelf();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturn(new stdClass());

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'foo1' => 'bar',
            'bar1' => '@foo',
            'foo2' => new stdClass(),
            'bar2' => '@@bar',
            'foo3' => '@bar',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMockObject(), $container, ['foo' => 'bar']);

        $this->assertInstanceOf(
            RecipeEndPoint::class,
            $endPoint(
                $managerMock,
                $request,
                $supervisor,
            )
        );
    }

    public function testInvokeWithRecipeWithContainerKeyNotFound(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getRecipeMock(), $container);

        $this->expectException(DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $this->createStub(CookingSupervisorInterface::class),
        );
    }

    public function testInvokeWithPlanWithContainerKeyNotFound(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getPlanMock(), $container);

        $this->expectException(DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $this->createStub(CookingSupervisorInterface::class),
        );
    }

    public function testInvokeWithBowlWithContainerKeyNotFound(): void
    {
        $managerMock = $this->createStub(ManagerInterface::class);

        $supervisor = $this->createStub(CookingSupervisorInterface::class);
        $this->getBowlMockObject()->expects($this->never())
            ->method('execute');

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn([
            'bar1' => '@foo',
        ]);

        $endPoint = new RecipeEndPoint($this->getBowlMockObject(), $container);

        $this->expectException(DomainException::class);
        $endPoint(
            $managerMock,
            $request,
            $supervisor,
        );
    }
}
