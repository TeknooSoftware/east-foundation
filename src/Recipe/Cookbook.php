<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorCookbookInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\CookbookInterface as BaseCookbookInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use TypeError;

/**
 * Base cookbook to execute HTTP request thanks to East Foundation with your framework.
 * The cookbook need an instance of Teknoo\East\Foundation\Recipe\RecipeInterface to be execute. By default this recipe
 * is empty but be prefilled by the DI.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Cookbook implements CookbookInterface
{
    use BaseCookbookTrait {
        fill as originalFill;
    }

    public function __construct(
        RecipeInterface $recipe,
        private readonly RouterInterface $router,
        private readonly ProcessorCookbookInterface $processorCookbook,
        private readonly LoopDetectorInterface $loopDetector,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(OriginalRecipeInterface $recipe): OriginalRecipeInterface
    {
        if ($recipe instanceof RecipeInterface) {
            $recipe = $recipe->registerMiddleware($this->router, RouterInterface::MIDDLEWARE_PRIORITY);
        }

        return $recipe->execute(
            $this->processorCookbook,
            ProcessorRecipeInterface::class,
            $this->loopDetector,
            ProcessorRecipeInterface::MIDDLEWARE_PRIORITY,
            true
        );
    }

    public function fill(OriginalRecipeInterface $recipe): BaseCookbookInterface
    {
        if (!$recipe instanceof RecipeInterface) {
            throw new TypeError('$recipe must be an instance of ' . RecipeInterface::class);
        }

        return $this->originalFill($recipe);
    }
}
