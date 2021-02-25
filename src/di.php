<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation;

use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Processor\LoopDetector;
use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorCookbook;
use Teknoo\East\Foundation\Processor\ProcessorCookbookInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Recipe\Cookbook;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;

use function DI\get;
use function DI\create;

return [
    Manager::class => get(ManagerInterface::class),
    ManagerInterface::class => static function (
        CookbookInterface $recipeCookbook
    ): ManagerInterface {
        $manager = new Manager();
        $manager->read($recipeCookbook);

        return $manager;
    },

    LoopDetector::class => get(LoopDetectorInterface::class),
    LoopDetectorInterface::class => create(LoopDetector::class),

    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => create(Processor::class),

    ProcessorRecipeInterface::class => static function (): ProcessorRecipeInterface {
        return new class extends Recipe implements ProcessorRecipeInterface {
        };
    },

    ProcessorCookbookInterface::class => get(ProcessorCookbook::class),
    ProcessorCookbook::class => create()
        ->constructor(
            get(ProcessorRecipeInterface::class),
            get(ProcessorInterface::class),
        ),

    RecipeInterface::class => get(Recipe::class),
    Recipe::class => create(),

    CookbookInterface::class => get(Cookbook::class),
    Cookbook::class => create()
        ->constructor(
            get(RecipeInterface::class),
            get(RouterInterface::class),
            get(ProcessorCookbookInterface::class),
            get(LoopDetectorInterface::class)
        ),
];
