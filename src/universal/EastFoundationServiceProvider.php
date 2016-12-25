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

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;

/**
 * Definition provider following PSR 11 Draft to build an universal bundle/package.
 */
class EastFoundationServiceProvider implements ServiceProvider
{
    /**
     * Constants to define services' keys in container.
     */
    const SERVICE_PROCESSOR = 'teknoo.east.processor';
    const SERVICE_MANAGER = 'teknoo.east.manager';

    /**
     * @param ContainerInterface $container
     *
     * @return ProcessorInterface
     */
    public static function createProcessor(ContainerInterface $container): ProcessorInterface
    {
        return new Processor($container->get(LoggerInterface::class));
    }

    /**
     * @return ManagerInterface
     */
    public static function createManager(): ManagerInterface
    {
        return new Manager();
    }

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            //teknoo.states.lifecyclable.service.tokenizer
            ProcessorInterface::class => [static::class, 'createProcessor'],
            static::SERVICE_PROCESSOR => [static::class, 'createProcessor'],

            //teknoo.states.lifecyclable.bridge.event_dispatcher
            ManagerInterface::class => [static::class, 'createManager'],
            static::SERVICE_MANAGER => [static::class, 'createManager'],
        ];
    }
}
