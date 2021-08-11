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

namespace Teknoo\East\Foundation\Promise;

use Teknoo\Recipe\Promise\Promise as BasePromise;
use Teknoo\Recipe\Promise\PromiseInterface as BaseInterface;
use Throwable;

use function trigger_error;

/**
 * With #East, methods and objects communicate via callback defined in interfaces. But it's not always possible to know
 * interfaces or classes of all actors. PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. : It'ss useful with east controller.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @deprecated Use Teknoo\Recipe\Promise\Promise directly
 */
class Promise extends BasePromise implements PromiseInterface
{
    public function __construct(callable $onSuccess = null, callable $onFail = null)
    {
        @trigger_error("Since 5.3.3, Use Teknoo\Recipe\Promise\Promise directly", E_USER_DEPRECATED);

        parent::__construct($onSuccess, $onFail);
    }

    public function next(?BaseInterface $promise = null): BaseInterface
    {
        return parent::next($promise);
    }

    public function success(mixed $result = null): PromiseInterface
    {
        parent::success($result);

        return $this;
    }

    public function fail(Throwable $throwable): PromiseInterface
    {
        parent::fail($throwable);

        return $this;
    }
}
