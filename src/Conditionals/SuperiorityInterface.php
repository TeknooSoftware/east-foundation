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

namespace Teknoo\East\Foundation\Conditionals;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define object able to test if an another object is great than it, and pass the result
 * to the promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 * @template TResultType
 */
interface SuperiorityInterface
{
    /**
     * To define object able to test if an another object is great than it, and pass the result
     * to the promise.
     *
     * @param PromiseInterface<TSuccessArgType, TResultType> $promise
     * @return SuperiorityInterface<TSuccessArgType, TResultType>
     */
    public function isGreaterThan(mixed $object, PromiseInterface $promise): SuperiorityInterface;
}
