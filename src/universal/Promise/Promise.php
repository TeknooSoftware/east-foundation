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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\Promise;

use Teknoo\Immutable\ImmutableTrait;

/**
 * With #East, methods and objects communicate via callback defined in interfaces. But it's not always possible to know
 * interfaces or classes of all actors. PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. : It'ss useful with east controller.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Promise implements PromiseInterface
{
    use ImmutableTrait;

    /**
     * @var callable|null
     */
    private $onSuccess;

    /**
     * @var callable|null
     */
    private $onFail;

    /**
     * @var PromiseInterface|null
     */
    private $nextPromise;

    /**
     * {@inheritdoc}
     */
    public function __construct(callable $onSuccess = null, callable $onFail = null)
    {
        $this->onSuccess = $onSuccess;
        $this->onFail = $onFail;

        $this->uniqueConstructorCheck();
    }

    /**
     * {@inheritdoc}
     */
    public function next(PromiseInterface $promise = null): PromiseInterface
    {
        $clone = clone $this;
        $clone->nextPromise = $promise;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function success($result = null): PromiseInterface
    {
        if (\is_callable($this->onSuccess)) {
            $onSuccess = ($this->onSuccess);
            $onSuccess($result, $this->nextPromise);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fail(\Throwable $throwable): PromiseInterface
    {
        if (\is_callable($this->onFail)) {
            $onFail = $this->onFail;
            $onFail($throwable, $this->nextPromise);
        }

        return $this;
    }
}
