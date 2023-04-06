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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\EndPoint;

use DomainException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\CookingSupervisorInterface;

use function is_string;
use function strlen;
use function substr;

/**
 * EndPoint wrapper to execute a recipe as endpoint The workplan is build with the server request and the client
 * instance (with keys `Psr\Http\Message\ServerRequestInterface` and `Teknoo\East\Foundation\Client\ClientInterface`).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RecipeEndPoint
{
    private BowlInterface $bowl;

    /**
     * @param array<string, mixed> $initialWorkPlan
     */
    public function __construct(
        BaseRecipeInterface|BowlInterface $recipe,
        private readonly ?ContainerInterface $container = null,
        private readonly array $initialWorkPlan = [],
    ) {
        if ($recipe instanceof BaseRecipeInterface) {
            $this->bowl = new RecipeBowl($recipe, 0);
        } else {
            $this->bowl = $recipe;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchWorkplan(ServerRequestInterface $request): array
    {
        if (null === $this->container) {
            return $this->initialWorkPlan;
        }

        $workplan = $this->initialWorkPlan;
        foreach ($request->getAttributes() as $name => $value) {
            if (
                !is_string($value)
                || 2 > strlen($value)
                || '@' !== $value[0]
            ) {
                //Element is already present into the workplan thanks to Processor, skip ip
                continue;
            }

            if ('@' === $value[0] && '@' === $value[1]) {
                //@@ escape, remove escape and add it

                $workplan[$name] = substr($value, 1);
                continue;
            }

            $key = substr($value, 1);
            if (!$this->container->has($key)) {
                throw new DomainException("The service '$key' is not available in the container");
            }

            $workplan[$name] = $this->container->get($key);
        }

        return $workplan;
    }

    public function __invoke(
        ManagerInterface $manager,
        ServerRequestInterface $request,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): RecipeEndPoint {
        $workPlan = $this->fetchWorkplan($request);
        $this->bowl->execute(
            $manager,
            $workPlan,
            $cookingSupervisor,
        );

        if (null !== $cookingSupervisor) {
            $cookingSupervisor->finish();
        }

        return $this;
    }
}
