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

namespace Teknoo\East\Foundation\Conditionals;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define object able to test if an another object is great or equal than it, and pass the result
 * to the promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 * @template TResultType
 */
interface HighInterface
{
    /**
     * To define object able to test if an another object is great or equal to it, and pass the result to the promise.
     *
     * @param PromiseInterface<TSuccessArgType, TResultType> $promise
     * @return HighInterface<TSuccessArgType, TResultType>
     */
    public function isUpperThan(mixed $object, PromiseInterface $promise): HighInterface;
}
