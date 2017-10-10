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
    Queue::class => get(QueueInterface::class),
    QueueInterface::class => function (): QueueInterface {
        return new Queue();
    },

    Manager::class => get(ManagerInterface::class),
    ManagerInterface::class => function (QueueInterface $queue): ManagerInterface {
        return new Manager($queue);
    },

    Processor::class => get(ProcessorInterface::class),
    ProcessorInterface::class => function (LoggerInterface $logger, ManagerInterface $manager): ProcessorInterface {
        $processor = new Processor($logger);

        $manager->registerMiddleware($processor, ProcessorInterface::MIDDLEWARE_PRIORITY);

        return $processor;
    },
];
