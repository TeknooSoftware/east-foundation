<?php

/**
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

namespace Teknoo\East\Foundation\EndPoint;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * EndPoint wrapper to execute a recipe as endpoint The workplan is build with the server request and the client
 * instance (with keys "request" and "client").
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeEndPoint
{
    private BaseRecipeInterface $recipe;

    private ?ContainerInterface $container;

    /**
     * @param RecipeInterface|CookbookInterface $recipe
     */
    public function __construct(BaseRecipeInterface $recipe, ?ContainerInterface $container = null)
    {
        $this->recipe = $recipe;
        $this->container = $container;
    }

    private function fetchWorkplan(ServerRequestInterface $request): array
    {
        if (null === $this->container) {
            return [];
        }

        $workplan = [];
        foreach ($request->getAttributes() as $name => $value) {
            if (
                !\is_string($value)
                || 2 > \strlen($value)
                || '@' !== $value[0]
            ) {
                //Element is already present into the workplan thanks to Processor, skip ip
                continue;
            }

            if ('@' === $value[0] && '@' === $value[1]) {
                //@@ escape, remove escape and add it

                $workplan[$name] = \substr($value, 1);
                continue;
            }

            $key = \substr($value, 1);
            if (!$this->container->has($key)) {
                throw new \DomainException("The service '$key' is not available in the container");
            }

            $workplan[$name] = $this->container->get($key);
        }

        return $workplan;
    }

    public function __invoke(
        ManagerInterface $manager,
        ServerRequestInterface $request
    ): RecipeEndPoint {
        $manager = $manager->reserveAndBegin($this->recipe);

        $manager->process($this->fetchWorkplan($request));

        return $this;
    }
}
