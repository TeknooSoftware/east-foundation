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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class EastFoundationCompilerPassTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationCompilerPass
 */
class EastFrameworkCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @return ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getContainerBuilderMock()
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(ContainerBuilder::class);
        }

        return $this->container;
    }

    /**
     * @return EastFoundationCompilerPass
     */
    private function buildCompilerPass(): EastFoundationCompilerPass
    {
        return new EastFoundationCompilerPass();
    }

    /**
     * @return string
     */
    public function getCompilerPassClass(): string
    {
        return EastFoundationCompilerPass::class;
    }

    public function testProcess()
    {
        $def = $this->createMock(Definition::class);
        $def->expects($this->exactly(2))->method('addMethodCall')->willReturnSelf();

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('has')
            ->willReturn(true);

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('findTaggedServiceIds')
            ->with('east.endpoint.template')
            ->willReturn([
                'service1' => ['foo' => 'bar'],
                'service2' => ['bar' => 'foo'],
            ]);

        $this->getContainerBuilderMock()
            ->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive(['service1'], ['service2'])
            ->willReturn($def);

        self::assertInstanceOf(
            $this->getCompilerPassClass(),
            $this->buildCompilerPass()->process(
                $this->getContainerBuilderMock()
            )
        );
    }

    public function testProcessNoTwig()
    {
        $def = $this->createMock(Definition::class);
        $def->expects($this->exactly(0))->method('addMethodCall')->willReturnSelf();

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('has')
            ->willReturnCallback(function ($value) {
                return 'twig' != $value;
            });

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('findTaggedServiceIds')
            ->with('east.endpoint.template')
            ->willReturn([
                'service1' => ['foo' => 'bar'],
                'service2' => ['bar' => 'foo'],
            ]);

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('getDefinition')
            ->withConsecutive(['service1'], ['service2'])
            ->willReturn($def);

        self::assertInstanceOf(
            $this->getCompilerPassClass(),
            $this->buildCompilerPass()->process(
                $this->getContainerBuilderMock()
            )
        );
    }
    
    public function testProcessError()
    {
        $this->expectException(\TypeError::class);
        $this->buildCompilerPass()->process(new \stdClass());
    }
}
