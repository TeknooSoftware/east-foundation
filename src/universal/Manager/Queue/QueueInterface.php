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

use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

/**
 * Interface to define queue managing middleware, in prioritized list of middleware to iterate when a request.
 * Need compile the list before iterate them to avoir list modification during an execution.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface QueueInterface
{
    /**
     * To register a middleware to the queue to call at each request.
     *
     * @param MiddlewareInterface $middleware
     * @param int $priority
     * @return QueueInterface
     */
    public function add(MiddlewareInterface $middleware, int $priority = 10): QueueInterface;

    /**
     * To compile and transform the queue as an runnable list and transform this queue as an immutable queue.
     *
     * @return QueueInterface
     */
    public function build(): QueueInterface;

    /**
     * To iterate, according to priority defined, each middleware in a foreach.
     *
     * @return mixed
     */
    public function iterate();

    /**
     * To stop the iteration, at next call of iterate, the loop will be broken.
     *
     * @return QueueInterface
     */
    public function stop(): QueueInterface;
}
