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
namespace Teknoo\East\Foundation\Processor;

use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Class Processor to instantiate controller action and pass the request.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Processor implements ProcessorInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param ContainerInterface $container
     * @param LoggerInterface    $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function executeRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        array $requestParameters
    ): ProcessorInterface {
        $processor = clone $this;
        $processor->doExecuteRequest($client, $request, $requestParameters);

        return $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function doExecuteRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        array $requestParameters
    ): ProcessorInterface {
        $controller = null;
        $arguments = null;
        try {
            //Get controller
            $controller = $this->getController($request, $requestParameters);
            // controller arguments
            $arguments = $this->getArguments($client, $request, $controller);
            //execute the controller
        } catch (\InvalidArgumentException $e) {
            $controller = null;
            $arguments = null;

            $this->logger->info('East Processor: '.$e->getMessage());
        }

        if (\is_callable($controller)) {
            $this->callController($controller, $arguments);
        }

        return $this;
    }

    /**
     * To call the controller and pass it the request and all params.
     *
     * @param callable $controller
     * @param array    $arguments
     */
    private function callController(callable $controller, array $arguments)
    {
        $controller(...$arguments);
    }

    /**
     * Analyze the request's params from the router to find and instantiate the controller.
     *
     * @param ServerRequestInterface ServerRequestInterface $request
     * @param array                                         $requestParameters
     *
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

        //If the controller is referenced by canonical class name / function name
        if (\is_string($requestParameters['_controller']) && false === \strpos($controller, ':')) {
            if (\method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (\function_exists($controller)) {
                return $controller;
            }
        }

        //If the controller value is a callable instance, returns it directly
        if (!\is_string($controller) && \is_callable($controller)) {
            return $controller;
        }

        //Controller is referenced as Controller in Symfony format ("ControllerName:ActionName")
        $callable = $this->createController($controller);

        if (!\is_callable($callable)) {
            throw new \InvalidArgumentException(
                \sprintf('The controller for URI "%s" is not callable.', $request->getUri())
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
        if (false === \strpos($controller, '::')) {
            throw new \InvalidArgumentException(\sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = \explode('::', $controller, 2);

        if (!\class_exists($class) && !$this->container->has($class)) {
            throw new \InvalidArgumentException(\sprintf('Class "%s" does not exist.', $class));
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
     * Analyze the request's params from the router to prepares parameters to inject to the controller before the
     * request processing.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param callable|object        $controller
     *
     * @return array
     */
    protected function getArguments(
        ClientInterface $client,
        ServerRequestInterface $request,
        callable $controller
    ): array {
        if (\is_array($controller)) { //Reflection the method's argument in the controller class
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (\is_object($controller) && !$controller instanceof \Closure) {
            //Reflection the method's arguments of the callable object
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($client, $request, $controller, $r->getParameters());
    }

    /**
     * Parse arguments needed by the controller method (class's method, function or closure) to inject in the good order
     * values from the request. Detect also parameters needed the client instance and the server request instance to
     * pass them, like Symfony with Request instance.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param callable               $controller
     * @param \ReflectionParameter[] $parameters
     *
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
            if (\array_key_exists($param->name, $attributes)) {
                //Parameter's value is available in the request
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                //The parameter need a instance of the request, pass it
                $arguments[] = $request;
            } elseif ($param->getClass() && $param->getClass()->isInstance($client)) {
                //The parameter need a instance of the client, pass it
                $arguments[] = $client;
            } elseif ($param->isDefaultValueAvailable()) {
                //The parameter's value is not available in the request but has a default value, get it
                $arguments[] = $param->getDefaultValue();
            } else {
                //The parameter's value is not available in the request and has not a default value.
                //Throw an exception, all values are needed to avoid PHP error.
                if (\is_array($controller)) {
                    $repr = \sprintf('%s::%s()', \get_class($controller[0]), $controller[1]);
                } elseif (\is_object($controller)) {
                    $repr = \get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(
                    \sprintf(
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
