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
 * to contact@uni-alteri.com so we can send you a copy immediately.
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
        return 'Teknoo\East\Framework\DependencyInjection\EastFrameworkExtension';
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
