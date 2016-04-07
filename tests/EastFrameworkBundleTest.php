<?php

namespace Teknoo\Tests\East\Framework;

use Teknoo\East\Framework\EastFrameworkBundle;

/**
 * Class EastFrameworkBundleTest
 * @package Teknoo\Tests\East\Framework
 * @covers Teknoo\East\Framework\EastFrameworkBundle
 */
class EastFrameworkBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFrameworkBundle
     */
    private function buildBundle(): EastFrameworkBundle
    {
        return new EastFrameworkBundle();
    }

    /**
     * @return string
     */
    private function getBundleClass(): string
    {
        return 'Teknoo\East\Framework\EastFrameworkBundle';
    }

    public function testBuild()
    {
        $this->assertInstanceOf(
            $this->getBundleClass(),
            $this->buildBundle()->build(
                $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', [], [], '', false)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testBuildErrorContainer()
    {
        $this->buildBundle()->build(new \stdClass());
    }
}