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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\Router;

use Teknoo\Immutable\ImmutableInterface;

/**
 * Interface ResultInterface to represent immutable object implementing a result of a router. A router can return
 * several results for a same request. Nexts results are available from the method 'getNext'.
 */
interface ResultInterface extends ImmutableInterface
{
    /**
     * To know the controller to call for this route.
     *
     * @return callable
     */
    public function getController(): callable;

    /**
     * To list all parameters of the controllers.
     *
     * @return ParameterInterface[];
     */
    public function getParameters(): array;

    /**
     * If there are several result found by the router for a same request, next router result can be fetched by this
     * method. Else this method must return null.
     *
     * @return ResultInterface|null
     */
    public function getNext();
}
