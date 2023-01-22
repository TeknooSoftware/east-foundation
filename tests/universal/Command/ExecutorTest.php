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

namespace Teknoo\Tests\East\Foundation\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Foundation\Command\Executor
 */
class ExecutorTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    private function getManagerMock(): ManagerInterface&MockObject
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    private function buildExecutor(): Executor
    {
        return new Executor(
            $this->getManagerMock()
        );
    }

    public function testExecuteBadRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildExecutor()->execute(
            new \stdClass(),
            $this->createMock(MessageInterface::class),
            $this->createMock(ClientInterface::class),
            [],
        );
    }

    public function testExecuteBadWorkPlan()
    {
        $this->expectException(\TypeError::class);
        $this->buildExecutor()->execute(
            $this->createMock(BaseRecipeInterface::class),
            $this->createMock(MessageInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
        );
    }

    public function testExecuteBadMessage()
    {
        $this->expectException(\TypeError::class);
        $this->buildExecutor()->execute(
            $this->createMock(BaseRecipeInterface::class),
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            [],
        );
    }

    public function testExecuteBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildExecutor()->execute(
            $this->createMock(BaseRecipeInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            [],
        );
    }

    public function testExecute()
    {
        $executor = $this->buildExecutor();
        self::assertInstanceOf(
            Executor::class,
            $executor->execute(
                $this->createMock(BaseRecipeInterface::class),
                $this->createMock(MessageInterface::class),
                $this->createMock(ClientInterface::class),
                []
            )
        );
    }

    public function testExecuteWithRecipe()
    {
        $executor = $this->buildExecutor();
        self::assertInstanceOf(
            Executor::class,
            $executor->execute(
                $this->createMock(Recipe::class),
                $this->createMock(MessageInterface::class),
                $this->createMock(ClientInterface::class),
                []
            )
        );
    }

    public function testExecuteTwoTimes()
    {
        $executor = new Executor(new Manager());
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects(self::any())->method('train')->willReturnCallback(
            function (ChefInterface $chef) use ($recipe) {
                $chef->followSteps([$this->createMock(BowlInterface::class)]);

                return $recipe;
            }
        );

        self::assertInstanceOf(
            Executor::class,
            $executor->execute(
                $recipe,
                $this->createMock(MessageInterface::class),
                $this->createMock(ClientInterface::class),
                []
            )
        );

        self::assertInstanceOf(
            Executor::class,
            $executor->execute(
                $recipe,
                $this->createMock(MessageInterface::class),
                $this->createMock(ClientInterface::class),
                []
            )
        );
    }
}
