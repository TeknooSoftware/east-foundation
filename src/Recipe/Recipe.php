<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\Recipe\Recipe as BaseRecipe;

/**
 * Recipe implementation built on Teknoo/Recipe implementation to define middleware registration into a recipe like
 * a step of the recipe. The class name of the middleware is used as step's name.
 * The methode "execute" of the middleware is used as callable.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Recipe extends BaseRecipe implements RecipeInterface
{
    public function registerMiddleware(
        MiddlewareInterface $middleware,
        int $priority = 10,
        string $middlewareName = null
    ): RecipeInterface {
        if (empty($middlewareName)) {
            $middlewareName = $middleware::class;
        }

        return $this->cook(
            $middleware->execute(...),
            $middlewareName,
            [],
            $priority
        );
    }
}
