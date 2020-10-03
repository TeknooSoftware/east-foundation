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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ProcessorCookbook implements ProcessorCookbookInterface
{
    private ProcessorRecipeInterface $recipe;

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

    public function fill(OriginalRecipeInterface $recipe): CookbookInterface
    {
        if (!$recipe instanceof ProcessorRecipeInterface) {
            throw new \TypeError('$recipe must be an instance of ProcessorRecipeInterface');
        }

        $this->recipe = $recipe;

        return $this;
    }

    public function train(ChefInterface $chef): BaseRecipeInterface
    {
        $this->recipe = $this->populateRecipe();

        $chef->read($this->recipe);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
    {
        $this->recipe->prepare($workPlan, $chef);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate($value): BaseRecipeInterface
    {
        $this->recipe->validate($value);

        return $this;
    }
}
