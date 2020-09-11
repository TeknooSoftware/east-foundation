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

namespace Teknoo\East\Foundation\Router;

use Teknoo\Immutable\ImmutableTrait;

/**
 * Immutable object implementing a result of a router. A router can return
 * several results for a same request. Next results are available from the method 'getNext'.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Result implements ResultInterface
{
    use ImmutableTrait;

    /**
     * @var callable
     */
    private $controller;

    /**
     * @var ParameterInterface[]
     */
    private ?array $parameters = null;

    private ?ResultInterface $next;

    public function __construct(callable $controller, ?ResultInterface $next = null)
    {
        $this->uniqueConstructorCheck();

        $this->controller = $controller;
        $this->next = $next;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(): callable
    {
        return $this->controller;
    }

    /**
     * To generate the \Reflection object dedicated to the controller. The controller is a callable, so it can be
     * a method of an object, a invokable object, or a function.
     *
     * @return \ReflectionFunction|\ReflectionMethod
     * @throws \ReflectionException
     */
    private function getReflectionInstance(): \ReflectionFunctionAbstract
    {
        if (\is_array($this->controller) && 2 === \count($this->controller)) {
            //Reflection the method's argument in the controller class
            return new \ReflectionMethod($this->controller[0], $this->controller[1]);
        }

        if (\is_object($this->controller) && !$this->controller instanceof \Closure) {
            //Reflection the method's arguments of the callable object
            $controllerReflected = new \ReflectionObject($this->controller);

            return $controllerReflected->getMethod('__invoke');
        }

        return new \ReflectionFunction($this->controller);
    }

    /**
     * To extract controller's parameter from \Reflection Api and convert into ParameterInterface instance.
     *
     * @return array<string, ParameterInterface>
     * @throws \ReflectionException
     */
    private function extractArguments(): array
    {
        $parameters = [];

        //Use the Reflection API to create Parameter Value object
        foreach ($this->getReflectionInstance()->getParameters() as $param) {
            $name = $param->getName();
            $hasDefault = $param->isDefaultValueAvailable();

            //Default value. To null if the parameter has no default value
            $defaultValue = null;
            if (true === $hasDefault) {
                $defaultValue = $param->getDefaultValue();
            }
            $class = $param->getClass();

            $parameters[$name] = new Parameter($name, $hasDefault, $defaultValue, $class);
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getParameters(): array
    {
        if (null === $this->parameters) {
            $this->parameters = $this->extractArguments();
        }

        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNext(): ?ResultInterface
    {
        return $this->next;
    }
}