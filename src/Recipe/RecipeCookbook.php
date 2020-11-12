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

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorCookbookInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * Base cookbook to execute HTTP request thanks to East Foundation with your framework.
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
class RecipeCookbook implements RecipeCookbookInterface
{
    private RecipeInterface $recipe;

    private bool $recipePopulated = false;

    private RouterInterface $router;

    private ProcessorCookbookInterface $processorCookbook;

    private LoopDetectorInterface $loopDetector;

    public function __construct(
        RecipeInterface $recipe,
        RouterInterface $router,
        ProcessorCookbookInterface $processorCookbook,
        LoopDetectorInterface $loopDetector
    ) {
        $this->fill($recipe);

        $this->router = $router;
        $this->processorCookbook = $processorCookbook;
        $this->loopDetector = $loopDetector;
    }

    private function populateRecipe(): RecipeInterface
    {
        $recipe = $this->recipe->registerMiddleware($this->router, RouterInterface::MIDDLEWARE_PRIORITY);
        $recipe = $recipe->execute(
            $this->processorCookbook,
            ProcessorRecipeInterface::class,
            $this->loopDetector,
            ProcessorRecipeInterface::MIDDLEWARE_PRIORITY
        );

        return $recipe;
    }

    private function getRecipe(): RecipeInterface
    {
        if ($this->recipePopulated) {
            return $this->recipe;
        }

        $this->recipe = $this->populateRecipe();
        $this->recipePopulated = true;

        return $this->recipe;
    }


    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function fill(OriginalRecipeInterface $recipe): CookbookInterface
    {
        if (!$recipe instanceof RecipeInterface) {
            throw new \TypeError('$recipe must be an instance of ' . RecipeInterface::class);
        }

        $this->recipe = $recipe;
        $this->recipePopulated = false;

        return $this;
    }
}
