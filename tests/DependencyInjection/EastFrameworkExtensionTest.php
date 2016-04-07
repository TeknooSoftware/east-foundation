<?php

namespace Teknoo\Tests\East\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\Framework\DependencyInjection\EastFrameworkExtension;

/**
 * Class EastFrameworkExtensionTest
 * @package Teknoo\Tests\East\Framework\DependencyInjection
 * @covers Teknoo\East\Framework\DependencyInjection\EastFrameworkExtension
 */
class EastFrameworkExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @return ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerBuilderMock(): ContainerBuilder
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->getMock(
                'Symfony\Component\DependencyInjection\ContainerInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->container;
    }

    /**
     * @return EastFrameworkExtension
     */
    private function buildExtension(): EastFrameworkExtension
    {
        return new EastFrameworkExtension();
    }

    /**
     * @return string
     */
    private function getExtensionClass(): string
    {
        return 'Symfony\Component\DependencyInjection\ContainerBuilder';
    }
    
    public function testLoad()
    {
        $this->assertInstanceOf(
            $this->getExtensionClass(),
            $this->buildExtension()->load([], $this->getContainerBuilderMock())
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testLoadErrorContainer()
    {
        $this->buildExtension()->load([], new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testLoadErrorConfig()
    {
        $this->buildExtension()->load(new \stdClass(), $this->getContainerBuilderMock());
    }
}
