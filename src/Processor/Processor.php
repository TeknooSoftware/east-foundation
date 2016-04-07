<?php

namespace Teknoo\East\Framework\Processor;

use Teknoo\East\Framework\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Processor
 * @package Teknoo\East\Framework\Processor
 */
class Processor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Processor constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function executeRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        array $requestParameters
    ): ProcessorInterface
    {
        //Get controller
        $controller = $this->getController($request, $requestParameters);
        // controller arguments
        $arguments = $this->getArguments($client, $request, $controller);
        //execute the controller
        $this->callController($controller, $arguments);

        return $this;
    }

    /**
     * @param callable $controller
     * @param array $arguments
     */
    private function callController(callable $controller, array $arguments)
    {
        $controller(...$arguments);
    }

    /**
     * @param ServerRequestInterface ServerRequestInterface $request
     * @param array $requestParameters
     * @return callable
     */
    protected function getController(ServerRequestInterface $request, array $requestParameters): callable
    {
        if (empty($requestParameters['_controller'])) {
            throw new \InvalidArgumentException(
                sprintf('The controller for URI "%s" is not callable.', $request->getUri())
            );
        }

        $controller = $requestParameters['_controller'];

        if (is_string($requestParameters['_controller']) && false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        if (!is_string($controller) && is_callable($controller)) {
            return $controller;
        }

        $callable = $this->createController($controller);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(
                sprintf('The controller for URI "%s" is not callable.', $request->getUri())
            );
        }

        return $callable;
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return callable A PHP callable
     *
     * @throws \InvalidArgumentException
     */
    private function createController(string $controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class) && !$this->container->has($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        return array($this->instantiateController($class), $method);
    }

    /**
     * Returns an instantiated controller.
     *
     * @param string $class A class name
     *
     * @return object
     */
    private function instantiateController(string $class)
    {
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }

        return new $class();
    }

    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @param callable|object $controller
     * @return array
     */
    protected function getArguments(ClientInterface $client, ServerRequestInterface $request, callable $controller): array
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($client, $request, $controller, $r->getParameters());
    }

    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @param callable $controller
     * @param \ReflectionParameter[] $parameters
     * @return array
     */
    private function doGetArguments(
        ClientInterface $client,
        ServerRequestInterface $request,
        callable $controller,
        array $parameters
    ): array {
        $attributes = $request->getAttributes();
        $arguments = array();
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->getClass() && $param->getClass()->isInstance($client)) {
                $arguments[] = $client;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(
                    sprintf(
                        'Controller "%s" requires that you provide a value for the "$%s" argument '
                        .'(because there is no default value or because there is a non optional argument after this one).',
                        $repr, $param->name
                    )
                );
            }
        }

        return $arguments;
    }
}