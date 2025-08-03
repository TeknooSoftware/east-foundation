<?php

/*
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

namespace Teknoo\East\Foundation;

use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Extension\Manager as ExtensionManager;
use Teknoo\East\Foundation\Extension\ManagerInterface as ExtensionManagerInterface;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Liveness\PingServiceInterface;
use Teknoo\East\Foundation\Liveness\TimeoutService;
use Teknoo\East\Foundation\Liveness\TimeoutServiceInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorPlan;
use Teknoo\East\Foundation\Processor\ProcessorPlanInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Recipe\Plan;
use Teknoo\East\Foundation\Recipe\PlanInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\Exception\PcntlNotAvailableException;
use Teknoo\East\Foundation\Time\SleepService;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\East\Foundation\Time\TimerServiceInterface;

use function DI\get;
use function DI\create;
use function DI\value;

return [
    'teknoo.east.client.must_send_response' => value(true),

    Manager::class => get(ManagerInterface::class),
    ManagerInterface::class => static function (
        PlanInterface $recipePlan
    ): ManagerInterface {
        $manager = new Manager();
        $manager->read($recipePlan);

        return $manager;
    },

    LoopDetector::class => get(LoopDetectorInterface::class),
    LoopDetectorInterface::class => create(LoopDetector::class),

    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => static function (ContainerInterface $container): ProcessorInterface {
        return new Processor(!$container->get('teknoo.east.client.must_send_response'));
    },

    ProcessorRecipeInterface::class => static function (): ProcessorRecipeInterface {
        return new class () extends Recipe implements ProcessorRecipeInterface {
        };
    },

    ProcessorPlanInterface::class => get(ProcessorPlan::class),
    ProcessorPlan::class => create()
        ->constructor(
            get(ProcessorRecipeInterface::class),
            get(ProcessorInterface::class),
        ),

    RecipeInterface::class => get(Recipe::class),
    Recipe::class => create(),

    PlanInterface::class => get(Plan::class),
    Plan::class => create()
        ->constructor(
            get(RecipeInterface::class),
            get(RouterInterface::class),
            get(ProcessorPlanInterface::class),
            get(LoopDetectorInterface::class)
        ),

    DatesService::class => create(),
    SleepServiceInterface::class => get(SleepService::class),
    SleepService::class => create()
        ->constructor(
            get(TimerServiceInterface::class),
        ),
    TimerServiceInterface::class => get(TimerService::class),
    TimerService::class => static function (ContainerInterface $container): TimerService {
        if (!TimerService::isAvailable()) {
            // @codeCoverageIgnoreStart
            throw new PcntlNotAvailableException("Error, the pcntl extension is available for this component");
            // @codeCoverageIgnoreEnd
        }

        return new TimerService(clone $container->get(DatesService::class));
    },

    PingServiceInterface::class => get(PingService::class),
    PingService::class => create(),
    TimeoutServiceInterface::class => get(TimeoutService::class),
    TimeoutService::class => static function (ContainerInterface $container): TimeoutService {
        $timerService = null;
        if (TimerService::isAvailable()) {
            $timerService = $container->get(TimerServiceInterface::class);
        }

        return new TimeoutService($timerService);
    },

    Executor::class => static function (): Executor {
        return new Executor(
            new Manager(),
        );
    },

    ExtensionManagerInterface::class => get(ExtensionManager::class),
    ExtensionManager::class => static fn () => ExtensionManager::run(),
];
