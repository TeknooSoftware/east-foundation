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

use Teknoo\Immutable\ImmutableInterface;

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
interface PromiseInterface extends ImmutableInterface
{
    /**
     * Initialize the promise with success and fail callback. Callback can be null, to exit silenty.
     *
     * @param callable|null $onSuccess
     * @param callable|null $onFail
     */
    public function __construct(callable $onSuccess = null, callable $onFail = null);

    /**
     * To define a new promise to pass to the called callback.
     *
     * @param PromiseInterface $promise
     *
     * @return PromiseInterface
     */
    public function next(PromiseInterface $promise = null): PromiseInterface;

    /**
     * To call the callback defined when the actor has successfully it's operation.
     *
     * @param mixed|null $result
     *
     * @return PromiseInterface
     */
    public function success($result = null): PromiseInterface;

    /**
     *To call the callback defined when an error has been occurred.
     *
     * @param \Throwable $throwable
     *
     * @return PromiseInterface
     */
    public function fail(\Throwable $throwable): PromiseInterface;
}
