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

namespace Teknoo\East\Foundation\Manager\Queue;

use Teknoo\East\Foundation\Manager\Queue\States\Editing;
use Teknoo\East\Foundation\Manager\Queue\States\Executing;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

class Queue implements
    QueueInterface,
    ImmutableInterface,
    ProxyInterface
{
    use ImmutableTrait,
        ProxyTrait;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareList = [];

    /**
     * @var MiddlewareInterface[]
     */
    private $compiledList = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * Manager constructor.
     * Initialize States behavior and Immutable behavior.
     */
    public function __construct()
    {
        //Call the method of the trait to initialize local attributes of the proxy
        $this->initializeProxy();
        //Behavior for Immutable
        $this->uniqueConstructorCheck();
        //Enable the main state "Service"
        $this->enableState(Editing::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function statesListDeclaration(): array
    {
        return [
            Editing::class,
            Executing::class
        ];
    }

    public function add(MiddlewareInterface $middleware, int $priority = 10): QueueInterface
    {
        $this->doRegister($middleware, $priority);

        return $this;
    }

    public function build(): QueueInterface
    {
        $this->compile();

        return $this;
    }

    public function iterate()
    {
        while ($this->position < \count($this->compiledList) && $this->position >= 0) {
            yield $this->compiledList[$this->position];
            $this->position++;
        };
    }

    public function stop(): QueueInterface
    {
        $this->doStop();

        return $this;
    }
}
