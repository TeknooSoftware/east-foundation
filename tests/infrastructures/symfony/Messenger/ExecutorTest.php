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

namespace Teknoo\Tests\East\FoundationBundle\Messenger;

use PHPUnit\Framework\MockObject\Stub;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Executor::class)]
class ExecutorTest extends TestCase
{
    private ?ManagerInterface $manager = null;

    private function getManagerMock(): ManagerInterface&Stub
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createStub(ManagerInterface::class);
        }

        return $this->manager;
    }

    private function buildExecutor(): Executor
    {
        return new Executor(
            $this->getManagerMock()
        );
    }

    public function testExecuteBadRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExecutor()->execute(
            new stdClass(),
            $this->createStub(MessageInterface::class),
            $this->createStub(ClientInterface::class),
            [],
        );
    }

    public function testExecuteBadWorkPlan(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExecutor()->execute(
            $this->createStub(BaseRecipeInterface::class),
            $this->createStub(MessageInterface::class),
            $this->createStub(ClientInterface::class),
            new stdClass(),
        );
    }

    public function testExecuteBadMessage(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExecutor()->execute(
            $this->createStub(BaseRecipeInterface::class),
            new stdClass(),
            $this->createStub(ClientInterface::class),
            [],
        );
    }

    public function testExecuteBadClient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExecutor()->execute(
            $this->createStub(BaseRecipeInterface::class),
            $this->createStub(ClientInterface::class),
            new stdClass(),
            [],
        );
    }

    public function testExecute(): void
    {
        $executor = $this->buildExecutor();
        $this->assertInstanceOf(
            Executor::class,
            $executor->execute(
                $this->createStub(BaseRecipeInterface::class),
                $this->createStub(MessageInterface::class),
                $this->createStub(ClientInterface::class),
                []
            )
        );
    }

    public function testExecuteTwoTimes(): void
    {
        $executor = new Executor(new Manager());
        $recipe = $this->createStub(RecipeInterface::class);
        $recipe->method('train')->willReturnCallback(
            function (ChefInterface $chef) use ($recipe): \Teknoo\Recipe\BaseRecipeInterface {
                $chef->followSteps([$this->createStub(BowlInterface::class)]);

                return $recipe;
            }
        );

        $this->assertInstanceOf(
            Executor::class,
            $executor->execute(
                $recipe,
                $this->createStub(MessageInterface::class),
                $this->createStub(ClientInterface::class),
                []
            )
        );

        $this->assertInstanceOf(
            Executor::class,
            $executor->execute(
                $recipe,
                $this->createStub(MessageInterface::class),
                $this->createStub(ClientInterface::class),
                []
            )
        );
    }
}
