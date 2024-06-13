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

namespace Teknoo\Tests\East\FoundationBundle\Messenger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Executor::class)]
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

    public function testExecuteTwoTimes()
    {
        $executor = new Executor(new Manager());
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->any())->method('train')->willReturnCallback(
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
