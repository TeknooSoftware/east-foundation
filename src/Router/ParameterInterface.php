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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Router;

use LogicException;
use ReflectionClass;
use Teknoo\Immutable\ImmutableInterface;

/**
 * Interface ParameterInterface to define ValueObject representing a parameter for a controller.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ParameterInterface extends ImmutableInterface
{
    /*
     * To get the name of the controller's parameter representing by the current instance.
     */
    public function getName(): string;

    /*
     * To know if the controller's parameter has a default value.
     */
    public function hasDefaultValue(): bool;

    /*
     * To get the default value of the controller's parameter.
     * If the parameter has no default value, the method must throw a \LogicException.
     *
     * @throws LogicException
     */
    public function getDefaultValue(): mixed;

    /*
     * To know if the parameter has a restriction on accepted class's instance for this controller's parameter.
     */
    public function hasClass(): bool;

    /**
     * To get the `\ReflectionClass` instance representing the accepted class's instance for this controller's
     * parameter. If the parameter has no default value, the method must throw a `\LogicException`.
     *
     * @return ReflectionClass<object>
     * @throws LogicException
     */
    public function getClass(): ReflectionClass;
}
