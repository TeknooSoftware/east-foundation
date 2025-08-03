<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Recipe;

use Teknoo\East\Foundation\Processor\LoopDetectorInterface;
use Teknoo\East\Foundation\Processor\ProcessorPlanInterface;
use Teknoo\East\Foundation\Processor\ProcessorRecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\PlanInterface as BasePlanInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use TypeError;

/**
 * Base plan to execute HTTP request thanks to East Foundation with your framework.
 * The plan need an instance of Teknoo\East\Foundation\Recipe\RecipeInterface to be execute. By default this recipe
 * is empty but be prefilled by the DI.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Plan implements PlanInterface
{
    use EditablePlanTrait {
        fill as originalFill;
    }

    public function __construct(
        RecipeInterface $recipe,
        private readonly RouterInterface $router,
        private readonly ProcessorPlanInterface $processorPlan,
        private readonly LoopDetectorInterface $loopDetector,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(OriginalRecipeInterface $recipe): OriginalRecipeInterface
    {
        $recipe = $recipe->cook(
            $this->router->execute(...),
            RouterInterface::class,
            [],
            RouterInterface::MIDDLEWARE_PRIORITY,
        );

        return $recipe->execute(
            $this->processorPlan,
            ProcessorRecipeInterface::class,
            $this->loopDetector,
            ProcessorRecipeInterface::MIDDLEWARE_PRIORITY,
            true
        );
    }

    public function fill(OriginalRecipeInterface $recipe): BasePlanInterface
    {
        if (!$recipe instanceof RecipeInterface) {
            throw new TypeError('$recipe must be an instance of ' . RecipeInterface::class);
        }

        return $this->originalFill($recipe);
    }
}
