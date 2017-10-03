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

namespace Teknoo\East\Foundation\Manager\Queue\States;

use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @property  MiddlewareInterface[] $middlewareList
 * @property  MiddlewareInterface[] $compiledList
 * @property  int $position
 */
class Editing implements StateInterface
{
    use StateTrait;

    /**
     * Builder to call to process a request in East Foundation by East's controller.
     *
     * @return \Closure
     */
    private function compile()
    {
        return function (): QueueInterface {
            /**
             * @var Queue $this
             */
            //Clone this queue, it is immutable and switch it's state
            $list = [];
            $listPrioritized = $this->middlewareList;
            ksort($listPrioritized);
            foreach ($listPrioritized as &$middlewareList) {
                foreach ($middlewareList as $middleware) {
                    $list[] = $middleware;
                }
            }

            $this->compiledList = $list;
            $this->position = 0;
            $this->switchState(Executing::class);

            return $this;
        };
    }

    /**
     * Builder to register middleware in the queue to process request.
     *
     * @return \Closure
     */
    private function doRegister()
    {
        /**
         * Method to register middleware in the queue to process request.
         *
         * @param MiddlewareInterface $middleware
         * @param int $priority
         *
         * @return QueueInterface
         */
        return function (MiddlewareInterface $middleware, int $priority = 10): QueueInterface {
            /**
             * @var Queue $this
             */
            $this->middlewareList[$priority][\spl_object_hash($middleware)] = $middleware;

            return $this;
        };
    }
}
