<?php
/**
 * East Framework.
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

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass;

/**
 * Class EastFrameworkCompilerPassTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass
 */
class EastFrameworkCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @return ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerBuilderMock()
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(
                'Symfony\Component\DependencyInjection\ContainerBuilder',
                [],
                [],
                '',
                false
            );
        }

        return $this->container;
    }
    
    /**
     * @return EastFrameworkCompilerPass
     */
    private function buildCompilerPass(): EastFrameworkCompilerPass
    {
        return new EastFrameworkCompilerPass();
    }

    /**
     * @return string
     */
    public function getCompilerPassClass(): string
    {
        return 'Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass';
    }

    public function testProcess()
    {
        $def = $this->createMock('Symfony\Component\DependencyInjection\Definition');
        $def->expects($this->exactly(2))->method('addMethodCall')->willReturnSelf();

        $this->getContainerBuilderMock()
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('east.controller.service')
            ->willReturn([
                'service1' => ['foo' => 'bar'],
                'service2' => ['bar' => 'foo']
            ]);

        $this->getContainerBuilderMock()
            ->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive(['service1'], ['service2'])
            ->willReturn($def);
        
        $this->assertInstanceOf(
            $this->getCompilerPassClass(),
            $this->buildCompilerPass()->process(
                $this->getContainerBuilderMock()
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testProcessError()
    {
        $this->buildCompilerPass()->process(new \stdClass());
    }
}
