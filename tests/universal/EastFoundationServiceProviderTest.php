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

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\EastFoundationServiceProvider;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\ProcessorInterface;

/**
 * Class DefinitionProviderTest
 * @covers \Teknoo\East\Foundation\EastFoundationServiceProvider
 */
class EastFoundationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFoundationServiceProvider
     */
    public function buildProvider(): EastFoundationServiceProvider
    {
        return new EastFoundationServiceProvider();
    }

    public function testGetDefinitions()
    {
        $definitions = $this->buildProvider()->getServices();
        self::assertTrue(isset($definitions[ProcessorInterface::class]));
        self::assertTrue(isset($definitions['teknoo.east.processor']));
        self::assertTrue(isset($definitions[ManagerInterface::class]));
        self::assertTrue(isset($definitions['teknoo.east.manager']));
    }

    public function testCreateManager()
    {
        self::assertInstanceOf(
            ManagerInterface::class,
            EastFoundationServiceProvider::createManager()
        );
    }

    public function testCreateProcessor()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::any())
            ->method('get')
            ->withConsecutive([LoggerInterface::class])
            ->willReturnOnConsecutiveCalls($this->createMock(LoggerInterface::class));

        self::assertInstanceOf(
            ProcessorInterface::class,
            EastFoundationServiceProvider::createProcessor($container)
        );
    }
}