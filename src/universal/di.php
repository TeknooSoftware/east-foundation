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
use function DI\object;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Recipe\Recipe;
use Teknoo\East\Foundation\Recipe\RecipeInterface;

return [
    Manager::class => get(ManagerInterface::class),
    ManagerInterface::class => function (
        RecipeInterface $recipe
    ): ManagerInterface {
        $manager = new Manager();
        $manager->read($recipe);

        return $manager;
    },

    Recipe::class => get(RecipeInterface::class),
    RecipeInterface::class => function (
        ProcessorInterface $processor
    ): RecipeInterface {
        $recipe = new Recipe();
        $recipe = $recipe->registerMiddleware($processor, ProcessorInterface::MIDDLEWARE_PRIORITY);

        return $recipe;
    },

    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => object(Processor::class),
];
