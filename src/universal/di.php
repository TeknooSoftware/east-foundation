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

namespace Teknoo\East\Foundation;

use function DI\get;
use function DI\create;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\Bowl\DynamicBowl;

return [
    Manager::class => get(ManagerInterface::class),
    ManagerInterface::class => function (
        RecipeInterface $recipe
    ): ManagerInterface {
        $manager = new Manager();
        $manager->read($recipe);

        return $manager;
    },

    LoopDetector::class => get(LoopDetectorInterface::class),
    LoopDetectorInterface::class => create(LoopDetector::class),

    ProcessorRecipeInterface::class => function (
        ProcessorInterface $processor
    ): ProcessorRecipeInterface {
        $recipe = new class extends Recipe implements ProcessorRecipeInterface {
        };

        $recipe = $recipe->registerMiddleware($processor, ProcessorInterface::MIDDLEWARE_PRIORITY);
        $recipe = $recipe->cook(
            new DynamicBowl(ProcessorInterface::WORK_PLAN_CONTROLLER_KEY, false),
            ProcessorInterface::WORK_PLAN_CONTROLLER_KEY,
            [],
            20
        );

        return $recipe;
    },

    Recipe::class => get(RecipeInterface::class),
    RecipeInterface::class => function (
        RouterInterface $router,
        ProcessorRecipeInterface $promiseRecipe,
        LoopDetectorInterface $loopDetector
    ): RecipeInterface {
        $recipe = new Recipe();

        $recipe = $recipe->registerMiddleware($router, RouterInterface::MIDDLEWARE_PRIORITY);
        $recipe = $recipe->execute(
            $promiseRecipe,
            ProcessorRecipeInterface::class,
            $loopDetector,
            ProcessorRecipeInterface::MIDDLEWARE_PRIORITY
        );

        return $recipe;
    },

    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => create(Processor::class),
];
