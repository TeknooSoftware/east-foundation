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

namespace Teknoo\Tests\East\Foundation;

use DI\Container;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;

/**
 * Class DefinitionProviderTest
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        return include(__DIR__.'/../../src/universal/generator.php');
    }


    public function testCreateManager()
    {
        $container = $this->buildContainer();
        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        self::assertInstanceOf(
            Manager::class,
            $manager1
        );

        self::assertInstanceOf(
            Manager::class,
            $manager2
        );

        self::assertSame($manager1, $manager2);
    }

    public function testCreateProcessor()
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $processor1 = $container->get(ProcessorInterface::class);
        $processor2 = $container->get(Processor::class);

        self::assertInstanceOf(
            Processor::class,
            $processor1
        );

        self::assertInstanceOf(
            Processor::class,
            $processor2
        );

        self::assertSame($processor1, $processor2);
    }
}