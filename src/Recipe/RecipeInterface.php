<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\Recipe\RecipeInterface as BaseInterface;

/**
 * Interface built on Teknoo/Recipe interface to define middleware registration into a recipe like
 * a step of the recipe. The class name of the middleware is used as step's name.
 * The methode "execute" of the middleware is used as callable.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RecipeInterface extends BaseInterface
{
    /*
     * Method to register middleware in the manager to process request.
     */
    public function registerMiddleware(
        MiddlewareInterface $middleware,
        int $priority = 10,
        ?string $middlewareName = null
    ): RecipeInterface;
}
