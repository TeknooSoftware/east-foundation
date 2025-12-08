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

namespace Teknoo\Tests\East\Foundation;

use PHPUnit\Framework\TestCase;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    protected function buildContainer(): Container
    {
        return include __DIR__ . '/../../src/generator.php';
    }

    public function testCreateManager(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createStub(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        $this->assertInstanceOf(
            Manager::class,
            $manager1
        );

        $this->assertInstanceOf(
            Manager::class,
            $manager2
        );

        $this->assertSame($manager1, $manager2);
    }

    public function testCreateProcessor(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createStub(LoggerInterface::class));
        $manager = $this->createStub(ManagerInterface::class);
        $container->set(ManagerInterface::class, $manager);
        $processor1 = $container->get(ProcessorInterface::class);
        $processor2 = $container->get(Processor::class);

        $this->assertInstanceOf(
            Processor::class,
            $processor1
        );

        $this->assertInstanceOf(
            Processor::class,
            $processor2
        );

        $this->assertSame($processor1, $processor2);
    }

    public function testLoopDetector(): void
    {
        $container = $this->buildContainer();
        $loopDetector1 = $container->get(LoopDetectorInterface::class);
        $loopDetector2 = $container->get(LoopDetector::class);

        $this->assertInstanceOf(
            LoopDetector::class,
            $loopDetector1
        );

        $this->assertInstanceOf(
            LoopDetector::class,
            $loopDetector2
        );

        $this->assertSame($loopDetector1, $loopDetector2);
    }

    public function testProcessorRecipe(): void
    {
        $container = $this->buildContainer();
        $recipe = $container->get(ProcessorRecipeInterface::class);

        $this->assertInstanceOf(
            ProcessorRecipeInterface::class,
            $recipe
        );
    }

    public function testRecipe(): void
    {
        $container = $this->buildContainer();
        $recipe1 = $container->get(Recipe::class);
        $recipe2 = $container->get(RecipeInterface::class);

        $this->assertInstanceOf(
            Recipe::class,
            $recipe1
        );

        $this->assertInstanceOf(
            Recipe::class,
            $recipe2
        );

        $this->assertSame($recipe1, $recipe2);
    }

    public function testProcessorPlan(): void
    {
        $container = $this->buildContainer();
        $plan1 = $container->get(ProcessorPlan::class);
        $plan2 = $container->get(ProcessorPlanInterface::class);

        $this->assertInstanceOf(
            ProcessorPlan::class,
            $plan1
        );

        $this->assertInstanceOf(
            ProcessorPlan::class,
            $plan2
        );

        $this->assertSame($plan1, $plan2);
    }

    public function testRecipePlan(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createStub(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $plan1 = $container->get(Plan::class);
        $plan2 = $container->get(PlanInterface::class);

        $this->assertInstanceOf(
            Plan::class,
            $plan1
        );

        $this->assertInstanceOf(
            Plan::class,
            $plan2
        );

        $this->assertSame($plan1, $plan2);
    }

    public function testDatesService(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            DatesService::class,
            $container->get(DatesService::class)
        );
    }

    public function testTimerService(): void
    {
        if (defined('PCNTL_MOCKED')) {
            self::markTestSkipped('PCNTL is not available');
        }

        $container = $this->buildContainer();
        $this->assertInstanceOf(
            TimerService::class,
            $container->get(TimerService::class)
        );
        $this->assertInstanceOf(
            TimerServiceInterface::class,
            $container->get(TimerServiceInterface::class)
        );
    }

    public function testPingService(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            PingService::class,
            $container->get(PingService::class)
        );
        $this->assertInstanceOf(
            PingServiceInterface::class,
            $container->get(PingServiceInterface::class)
        );
    }

    public function testTimeoutService(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            TimeoutService::class,
            $container->get(TimeoutService::class)
        );
        $this->assertInstanceOf(
            TimeoutServiceInterface::class,
            $container->get(TimeoutServiceInterface::class)
        );
    }

    public function testExecutor(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            Executor::class,
            $container->get(Executor::class)
        );
    }

    public function testExtensionManagerInterface(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            ExtensionManagerInterface::class,
            $container->get(ExtensionManagerInterface::class)
        );
    }

    public function testExtensionManager(): void
    {
        $container = $this->buildContainer();
        $this->assertInstanceOf(
            ExtensionManager::class,
            $container->get(ExtensionManager::class)
        );
    }
}
