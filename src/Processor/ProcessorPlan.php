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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Processor;

use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use TypeError;

/**
 * Base plan to execute the endpoint found by the manager in the main recipe for the current HTTP request thanks
 * to East Foundation with your framework.
 *
 * The plan need an instance of Teknoo\East\Foundation\Recipe\RecipeInterface to be execute. By default this recipe
 * is empty but be prefilled by the DI.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProcessorPlan implements ProcessorPlanInterface
{
    use EditablePlanTrait {
        fill as originalFill;
    }

    public function __construct(
        ProcessorRecipeInterface $recipe,
        private readonly ProcessorInterface $processor,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(OriginalRecipeInterface $recipe): OriginalRecipeInterface
    {
        $recipe = $recipe->cook(
            $this->processor->execute(...),
            ProcessorInterface::class,
            [],
            ProcessorInterface::MIDDLEWARE_PRIORITY,
        );

        return $recipe->cook(
            new DynamicBowl(ProcessorInterface::WORK_PLAN_CONTROLLER_KEY, false),
            ProcessorInterface::WORK_PLAN_CONTROLLER_KEY,
            [],
            20
        );
    }

    public function fill(OriginalRecipeInterface $recipe): PlanInterface
    {
        if (!$recipe instanceof ProcessorRecipeInterface) {
            throw new TypeError('$recipe must be an instance of ProcessorRecipeInterface');
        }

        return $this->originalFill($recipe);
    }
}
