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

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Recipe as BaseRecipe;
use Teknoo\Recipe\RecipeInterface as BaseRecipeInterface;

/**
 * Recipe implementation built on Teknoo/Recipe implementation to define middleware registration into a recipe like
 * a step of the recipe. The class name of the middleware is used as step's name.
 * The methode "execute" of the middleware is used as callable.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Recipe extends BaseRecipe implements RecipeInterface
{
    private ?BaseRecipeInterface $recipe = null;

    /**
     * @inheritDoc
     */
    public function fill(BaseRecipeInterface $recipe): CookbookInterface
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerMiddleware(
        MiddlewareInterface $middleware,
        int $priority = 10,
        string $middlewareName = null
    ): RecipeInterface {
        if (empty($middlewareName)) {
            $middlewareName = \get_class($middleware);
        }

        return $this->cook(
            [$middleware, 'execute'],
            $middlewareName,
            [],
            $priority
        );
    }

    /**
     * @inheritDoc
     */
    public function train(ChefInterface $chef): BaseRecipeInterface
    {
        if (null === $this->recipe) {
            return parent::train($chef);
        }

        $recipe = $this->recipe->cook(
            new RecipeBowl($this, 0),
            static::class
        );

        $recipe->train($chef);

        return $this;
    }
}
