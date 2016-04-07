<?php

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass;

/**
 * Class EastFrameworkCompilerPassTest
 * @package Teknoo\Tests\East\Framework\DependencyInjection
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
            $this->container = $this->getMock(
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
        $def = $this->getMock('Symfony\Component\DependencyInjection\Definition');
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