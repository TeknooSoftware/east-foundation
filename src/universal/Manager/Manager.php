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

namespace Teknoo\East\Foundation\Manager;

use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
use Teknoo\East\Foundation\Manager\States\Running;
use Teknoo\East\Foundation\Manager\States\Service;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * Class Manager to process requests in East Foundation. The manager
 * passes the request to each middleware as the spread has not been stopped.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @method ManagerInterface running(ClientInterface $client, ServerRequestInterface $request)
 * @method ManagerInterface doRegisterMiddleware(MiddlewareInterface $middleware)
 * @method ManagerInterface doStopPropagation()
 */
class Manager implements
    ManagerInterface,
    ImmutableInterface,
    ProxyInterface
{
    use ImmutableTrait,
        ProxyTrait;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * Manager constructor.
     * Initialize States behavior and Immutable behavior.
     */
    public function __construct()
    {
        $this->queue = new Queue();

        //Call the method of the trait to initialize local attributes of the proxy
        $this->initializeProxy();
        //Behavior for Immutable
        $this->uniqueConstructorCheck();
        //Enable the main state "Service"
        $this->enableState(Service::class);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->queue = clone $this->queue;
        $this->cloneProxy();
    }

    /**
     * {@inheritdoc}
     */
    public static function statesListDeclaration(): array
    {
        return [
            Running::class,
            Service::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function receiveRequestFromClient(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface {
        //Run this request
        return $this->running($client, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function continueExecution(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface {
        return $this->dispatchRequest($client, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function registerMiddleware(
        MiddlewareInterface $middleware,
        int $priority = 10
    ): ManagerInterface {
        return $this->doRegisterMiddleware($middleware, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation(): ManagerInterface
    {
        return $this->doStopPropagation();
    }
}
