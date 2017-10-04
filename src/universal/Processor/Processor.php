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

namespace Teknoo\East\Foundation\Processor;

use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Processor implementation to call each controller callable returned by the
 * router the PSR7 Server Request, the ClientInterface instance and other callable's argument founded in the request.
 *
 * If some arguments are missing in the request. The processor must throws exceptions.
 *
 * The Processor is independent of Symfony.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        $processor = clone $this;

        $routerResult = $request->getAttribute(RouterInterface::ROUTER_RESULT_KEY);
        if ($routerResult instanceof ResultInterface) {
            $processor->doExecute($client, $request, $routerResult);
        }

        return $processor;
    }

    /**
     * Method called to execute each controller retourned by the router and call the next controller defined in the
     * router's result.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param ResultInterface        $routerResult
     */
    private function doExecute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ResultInterface $routerResult
    ) {
        $controller = $routerResult->getController();
        $arguments = $this->getControllerParams($client, $request, $controller, $routerResult->getParameters());
        $this->callController($controller, $arguments);

        $next = $routerResult->getNext();
        if ($next instanceof ResultInterface) {
            $this->doExecute($client, $request, $next);
        }
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
     * To get the controller representation / it's string name.
     *
     * @param mixed $controller
     *
     * @return string
     */
    private function getControllerRepresentation($controller)
    {
        $repr = $controller;
        if (\is_object($controller)) {
            $repr = \get_class($controller);
        } elseif (\is_array($controller) && 2 >= \count($controller)) {
            if (\is_object($controller[0])) {
                $repr = \sprintf('%s::%s()', \get_class($controller[0]), $controller[1]);
            } else {
                $repr = \sprintf('%s::%s()', $controller[0], $controller[1]);
            }
        }

        return $repr;
    }

    /**
     * Parse arguments needed by the controller method (class's method, function or closure) to inject in the good order
     * values from the request. Detect also parameters needed the client instance and the server request instance to
     * pass them, like Symfony with Request instance.
     *
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param callable               $controller
     * @param ParameterInterface[]   $parameters
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getControllerParams(
        ClientInterface $client,
        ServerRequestInterface $request,
        callable $controller,
        array $parameters
    ): array {
        $attributes = array_merge($request->getAttributes(), (array) $request->getParsedBody());

        $arguments = array();
        /*
         * @var ParameterInterface
         */
        foreach ($parameters as $param) {
            $paramName = $param->getName();
            if ($param->hasClass() && $param->getClass()->isInstance($request)) {
                //The parameter need a instance of the request, pass it
                $arguments[] = $request;
                continue;
            } elseif ($param->hasClass() && $param->getClass()->isInstance($client)) {
                //The parameter need a instance of the client, pass it
                $arguments[] = $client;
                continue;
            } elseif (\array_key_exists($paramName, $attributes)
                && (!$param->hasClass() || $param->getClass()->isInstance($attributes[$paramName]))) {
                //Parameter's value is available in the request
                $arguments[] = $attributes[$paramName];
                continue;
            } elseif ($param->hasDefaultValue()) {
                //The parameter's value is not available in the request but has a default value, get it
                $arguments[] = $param->getDefaultValue();
                continue;
            }

            //The parameter's value is not available in the request and has not a default value.
            //Throw an exception, all values are needed to avoid PHP error.
            $repr = $this->getControllerRepresentation($controller);
            throw new \RuntimeException(
                \sprintf(
                    'Controller "%s" requires that you provide a value for the "$%s" argument '
                    .'(because there is no default value or because there is a non optional argument after this one).',
                    $repr,
                    $param->getName()
                )
            );
        }

        return $arguments;
    }
}
