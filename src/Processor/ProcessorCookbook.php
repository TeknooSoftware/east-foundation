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

use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;
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
    private ProcessorRecipeInterface $recipe;

    private bool $recipePopulated = false;

    private ProcessorInterface $processor;

    public function __construct(ProcessorRecipeInterface $recipe, ProcessorInterface $processor)
    {
        $this->fill($recipe);
        $this->processor = $processor;
    }

    private function populateRecipe(): ProcessorRecipeInterface
    {
        $recipe = $this->recipe->registerMiddleware($this->processor, ProcessorInterface::MIDDLEWARE_PRIORITY);
        $recipe = $recipe->cook(
            new DynamicBowl(ProcessorInterface::WORK_PLAN_CONTROLLER_KEY, false),
            ProcessorInterface::WORK_PLAN_CONTROLLER_KEY,
            [],
            20
        );

        return $recipe;
    }

    private function getRecipe(): ProcessorRecipeInterface
    {
        if ($this->recipePopulated) {
            return $this->recipe;
        }

        $this->recipe = $this->populateRecipe();
        $this->recipePopulated = true;

        return $this->recipe;
    }

    public function fill(OriginalRecipeInterface $recipe): CookbookInterface
    {
        if (!$recipe instanceof ProcessorRecipeInterface) {
            throw new \TypeError('$recipe must be an instance of ProcessorRecipeInterface');
        }

        $this->recipe = $recipe;
        $this->recipePopulated = false;

        return $this;
    }

    public function train(ChefInterface $chef): BaseRecipeInterface
    {
        $chef->read($this->getRecipe());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
    {
        $this->getRecipe()->prepare($workPlan, $chef);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate($value): BaseRecipeInterface
    {
        $this->getRecipe()->validate($value);

        return $this;
    }
}
