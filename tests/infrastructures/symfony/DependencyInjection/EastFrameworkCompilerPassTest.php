<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\DependencyInjection;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class EastFoundationCompilerPassTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationCompilerPass
 */
class EastFrameworkCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    private function getContainerBuilderMock(): ContainerBuilder&MockObject
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
            ->with(
                $this->callback(
                    fn ($value) => match ($value) {
                        'service1' => true,
                        'service2' => true,
                        default => false,
                    }
                )
            )
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
            ->willReturnCallback(fn($value) => 'twig' != $value);

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
            ->with(
                $this->callback(
                    fn ($value) => match ($value) {
                        'service1' => true,
                        'service2' => true,
                        default => false,
                    }
                )
            )
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
