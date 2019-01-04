<?php

declare(strict_types=1);

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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\EndPoint;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * EndPoint wrapper to execute a recipe as endpoint The workplan is build with the server request and the client
 * instance (with keys "request" and "client").
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RecipeEndPoint
{
    /**
     * @var RecipeInterface
     */
    private $recipe;

    /**
     * RecipeEndPoint constructor.
     * @param RecipeInterface $recipe
     */
    public function __construct(RecipeInterface $recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * @param ManagerInterface $manager
     * @return RecipeEndPoint
     */
    public function __invoke(
        ManagerInterface $manager
    ): RecipeEndPoint {
        $manager = $manager->reserveAndBegin($this->recipe);

        $manager->process([]);

        return $this;
    }
}
