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

namespace Teknoo\East\Foundation\Processor;

use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * Base cookbook to execute the endpoint found by the manager in the main recipe for the current HTTP request thanks
 * to East Foundation with your framework.
 *
 * The cookbook need an instance of Teknoo\East\Foundation\Recipe\RecipeInterface to be execute. By default this recipe
 * is empty but be prefilled by the DI.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ProcessorCookbook implements ProcessorCookbookInterface
{
    use BaseCookbookTrait {
        fill as originalFill;
    }

    private ProcessorInterface $processor;

    public function __construct(ProcessorRecipeInterface $recipe, ProcessorInterface $processor)
    {
        $this->fill($recipe);
        $this->processor = $processor;
    }

    protected function populateRecipe(OriginalRecipeInterface $recipe): OriginalRecipeInterface
    {
        if ($recipe instanceof ProcessorRecipeInterface) {
            $recipe = $recipe->registerMiddleware($this->processor, ProcessorInterface::MIDDLEWARE_PRIORITY);
        }

        $recipe = $recipe->cook(
            new DynamicBowl(ProcessorInterface::WORK_PLAN_CONTROLLER_KEY, false),
            ProcessorInterface::WORK_PLAN_CONTROLLER_KEY,
            [],
            20
        );

        return $recipe;
    }

    public function fill(OriginalRecipeInterface $recipe): CookbookInterface
    {
        if (!$recipe instanceof ProcessorRecipeInterface) {
            throw new \TypeError('$recipe must be an instance of ProcessorRecipeInterface');
        }

        return $this->originalFill($recipe);
    }
}
