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

namespace Teknoo\East\Foundation\Router;

use Teknoo\Immutable\ImmutableInterface;

/**
 * Interface ParameterInterface to define ValueObject representing a parameter for a controller.
 */
interface ParameterInterface extends ImmutableInterface
{
    /**
     * To get the name of the controller's parameter representing by the current instance.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * To know if the controller's parameter has a default value.
     *
     * @return bool
     */
    public function hasDefaultValue(): bool;

    /**
     * To get the default value of the controller's parameter.
     * If the parameter has no default value, the method must throw a \LogicException.
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    public function getDefaultValue();

    /**
     * To know if the parameter has a restriction on accepted class's instance for this controller's parameter.
     *
     * @return bool
     */
    public function hasClass(): bool;

    /**
     * To get the \ReflectionClass instance representing the accepted class's instance for this controller's parameter.
     * If the parameter has no default value, the method must throw a \LogicException.
     *
     * @return \ReflectionClass
     *
     * @throws \LogicException
     */
    public function getClass(): \ReflectionClass;
}
