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

namespace Teknoo\East\Foundation;

use function DI\get;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;

return [
    QueueInterface::class => get(Queue::class),
    Queue::class => function (): Queue {
        return new Queue();
    },

    ManagerInterface::class => get(Manager::class),
    Manager::class => function (QueueInterface $queue): Manager {
        return new Manager($queue);
    },

    ProcessorInterface::class => get(Processor::class),
    Processor::class => function (LoggerInterface $logger, ManagerInterface $manager): Processor {
        $processor = new Processor($logger);

        $manager->registerMiddleware($processor, ProcessorInterface::MIDDLEWARE_PRIORITY);

        return $processor;
    },
];
