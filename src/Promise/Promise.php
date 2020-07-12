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

namespace Teknoo\East\Foundation\Promise;

use Teknoo\Recipe\Promise\Promise as RecipePromise;
use Teknoo\Recipe\Promise\PromiseInterface as RecipePromiseInterface;

/**
 * With #East, methods and objects communicate via callback defined in interfaces. But it's not always possible to know
 * interfaces or classes of all actors. PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. : It'ss useful with east controller.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Promise extends RecipePromise implements PromiseInterface
{
    private ?PromiseInterface $nextPromise = null;

    /**
     * {@inheritdoc}
     */
    public function next(?PromiseInterface $promise = null): PromiseInterface
    {
        $clone = clone $this;
        $clone->nextPromise = $promise;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function success($result = null): RecipePromiseInterface
    {
        parent::success($result, $this->nextPromise);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fail(\Throwable $throwable): RecipePromiseInterface
    {
        parent::fail($throwable, $this->nextPromise);

        return $this;
    }
}
