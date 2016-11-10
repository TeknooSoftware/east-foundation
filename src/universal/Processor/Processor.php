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
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param LoggerInterface    $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function executeRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        ResultInterface $routerResult
    ): ProcessorInterface {
        $processor = clone $this;
        $processor->doExecuteRequest($client, $request, $routerResult);

        return $processor;
    }

    /**
     * Method called to execute each controller retourned by the router and call the next controller defined in the
     * router's result
     * @param ClientInterface        $client
     * @param ServerRequestInterface $request
     * @param ResultInterface        $routerResult
     */
    private function doExecuteRequest(
        ClientInterface $client,
        ServerRequestInterface $request,
        ResultInterface $routerResult
    ) {
        $controller = $routerResult->getController();
        $arguments = $this->getControllerParams($client, $request, $controller, $routerResult->getParameters());
        $this->callController($controller, $arguments);

        $next = $routerResult->getNext();
        if ($next instanceof ResultInterface) {
            $this->doExecuteRequest($client, $request, $next);
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
     */
    private function getControllerParams(
        ClientInterface $client,
        ServerRequestInterface $request,
        callable $controller,
        array $parameters
    ): array {
        $attributes = $request->getAttributes();

        $arguments = array();
        /**
         * @var ParameterInterface $param
         */
        foreach ($parameters as $param) {
            if (\array_key_exists($param->getName(), $attributes)) {
                //Parameter's value is available in the request
                $arguments[] = $attributes[$param->getName()];
            } elseif ($param->hasClass() && $param->getClass()->isInstance($request)) {
                //The parameter need a instance of the request, pass it
                $arguments[] = $request;
            } elseif ($param->hasClass() && $param->getClass()->isInstance($client)) {
                //The parameter need a instance of the client, pass it
                $arguments[] = $client;
            } elseif ($param->hasDefaultValue()) {
                //The parameter's value is not available in the request but has a default value, get it
                $arguments[] = $param->getDefaultValue();
            } else {
                //The parameter's value is not available in the request and has not a default value.
                //Throw an exception, all values are needed to avoid PHP error.
                if (\is_object($controller)) {
                    $repr = \get_class($controller);
                } elseif (\is_array($controller) && 2 >= \count($controller)) {
                    $repr = \sprintf('%s::%s()', \get_class($controller[0]), $controller[1]);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(
                    \sprintf(
                        'Controller "%s" requires that you provide a value for the "$%s" argument '
                        .'(because there is no default value or because there is a non optional argument after this one).',
                        $repr, $param->getName()
                    )
                );
            }
        }

        return $arguments;
    }
}
