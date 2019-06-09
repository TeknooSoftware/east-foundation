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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\Conditionals;

use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * Interface to define object able to test if an another object is lower or equal then it, and pass the result
 * to the promise.
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface LowInterface
{
    /**
     * To define object able to test if an another object is lower or equal then it, and pass the result to the promise.
     *
     * @param LowInterface|object|mixed $object
     * @param PromiseInterface $promise
     * @return LowInterface
     */
    public function isLowerThan($object, PromiseInterface $promise): LowInterface;
}
