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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation;

use DI\Container;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Extension\Manager as ExtensionManager;
use Teknoo\East\Foundation\Extension\ManagerInterface as ExtensionManagerInterface;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Liveness\PingServiceInterface;
use Teknoo\East\Foundation\Liveness\TimeoutService;
use Teknoo\East\Foundation\Liveness\TimeoutServiceInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorPlan;
use Teknoo\East\Foundation\Processor\ProcessorPlanInterface;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Recipe\Plan;
use Teknoo\East\Foundation\Recipe\PlanInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\East\Foundation\Time\TimerServiceInterface;

use function defined;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        return include __DIR__ . '/../../src/generator.php';
    }

    public function testCreateManager()
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        self::assertInstanceOf(
            Manager::class,
            $manager1
        );

        self::assertInstanceOf(
            Manager::class,
            $manager2
        );

        self::assertSame($manager1, $manager2);
    }

    public function testCreateProcessor()
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $manager = $this->createMock(ManagerInterface::class);
        $container->set(ManagerInterface::class, $manager);
        $processor1 = $container->get(ProcessorInterface::class);
        $processor2 = $container->get(Processor::class);

        self::assertInstanceOf(
            Processor::class,
            $processor1
        );

        self::assertInstanceOf(
            Processor::class,
            $processor2
        );

        self::assertSame($processor1, $processor2);
    }

    public function testLoopDetector()
    {
        $container = $this->buildContainer();
        $loopDetector1 = $container->get(LoopDetectorInterface::class);
        $loopDetector2 = $container->get(LoopDetector::class);

        self::assertInstanceOf(
            LoopDetector::class,
            $loopDetector1
        );

        self::assertInstanceOf(
            LoopDetector::class,
            $loopDetector2
        );

        self::assertSame($loopDetector1, $loopDetector2);
    }

    public function testProcessorRecipe()
    {
        $container = $this->buildContainer();
        $recipe = $container->get(ProcessorRecipeInterface::class);

        self::assertInstanceOf(
            ProcessorRecipeInterface::class,
            $recipe
        );
    }

    public function testRecipe()
    {
        $container = $this->buildContainer();
        $recipe1 = $container->get(Recipe::class);
        $recipe2 = $container->get(RecipeInterface::class);

        self::assertInstanceOf(
            Recipe::class,
            $recipe1
        );

        self::assertInstanceOf(
            Recipe::class,
            $recipe2
        );

        self::assertSame($recipe1, $recipe2);
    }

    public function testProcessorPlan()
    {
        $container = $this->buildContainer();
        $plan1 = $container->get(ProcessorPlan::class);
        $plan2 = $container->get(ProcessorPlanInterface::class);

        self::assertInstanceOf(
            ProcessorPlan::class,
            $plan1
        );

        self::assertInstanceOf(
            ProcessorPlan::class,
            $plan2
        );

        self::assertSame($plan1, $plan2);
    }

    public function testRecipePlan()
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $plan1 = $container->get(Plan::class);
        $plan2 = $container->get(PlanInterface::class);

        self::assertInstanceOf(
            Plan::class,
            $plan1
        );

        self::assertInstanceOf(
            Plan::class,
            $plan2
        );

        self::assertSame($plan1, $plan2);
    }

    public function testDatesService()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            DatesService::class,
            $container->get(DatesService::class)
        );
    }

    public function testTimerService()
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $container = $this->buildContainer();
        self::assertInstanceOf(
            TimerService::class,
            $container->get(TimerService::class)
        );
        self::assertInstanceOf(
            TimerServiceInterface::class,
            $container->get(TimerServiceInterface::class)
        );
    }

    public function testPingService()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            PingService::class,
            $container->get(PingService::class)
        );
        self::assertInstanceOf(
            PingServiceInterface::class,
            $container->get(PingServiceInterface::class)
        );
    }

    public function testTimeoutService()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            TimeoutService::class,
            $container->get(TimeoutService::class)
        );
        self::assertInstanceOf(
            TimeoutServiceInterface::class,
            $container->get(TimeoutServiceInterface::class)
        );
    }

    public function testExecutor()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            Executor::class,
            $container->get(Executor::class)
        );
    }

    public function testExtensionManagerInterface()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            ExtensionManagerInterface::class,
            $container->get(ExtensionManagerInterface::class)
        );
    }

    public function testExtensionManager()
    {
        $container = $this->buildContainer();
        self::assertInstanceOf(
            ExtensionManager::class,
            $container->get(ExtensionManager::class)
        );
    }
}
