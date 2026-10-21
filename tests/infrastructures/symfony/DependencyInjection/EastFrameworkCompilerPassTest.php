<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationCompilerPass;
use Symfony\Component\DependencyInjection\Definition;
use Teknoo\East\FoundationBundle\Router\AccessCheckerInterface;
use Teknoo\East\FoundationBundle\Router\AccessControlChecker;

/**
 * Class EastFoundationCompilerPassTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EastFoundationCompilerPass::class)]
class EastFrameworkCompilerPassTest extends TestCase
{
    private ?ContainerBuilder $container = null;

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

    public function testProcess(): void
    {
        $def = $this->createMock(Definition::class);
        $def->expects($this->exactly(2))->method('addMethodCall')->willReturnSelf();

        $this->getContainerBuilderMock()
            ->method('has')
            ->willReturn(true);

        $this->getContainerBuilderMock()
            ->expects($this->atLeastOnce())
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
                    fn ($value): bool => match ($value) {
                        'service1' => true,
                        'service2' => true,
                        default => false,
                    }
                )
            )
            ->willReturn($def);

        $this->buildCompilerPass()->process(
            $this->getContainerBuilderMock()
        );
        $this->assertTrue(true);
    }

    public function testProcessNoTwig(): void
    {
        $def = $this->createMock(Definition::class);
        $def->expects($this->exactly(0))->method('addMethodCall')->willReturnSelf();

        $container = $this->createStub(ContainerBuilder::class);

        $container
            ->method('has')
            ->willReturnCallback(fn (string $value): bool => 'twig' != $value);

        $container
            ->method('findTaggedServiceIds')
            ->willReturn([
                'service1' => ['foo' => 'bar'],
                'service2' => ['bar' => 'foo'],
            ]);

        $container
            ->method('getDefinition')
            ->willReturn($def);

        $this->buildCompilerPass()->process(
            $container
        );
        $this->assertTrue(true);
    }

    public function testProcessError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildCompilerPass()->process(new stdClass());
    }

    private function buildContainerWithAccessChecker(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register(AccessControlChecker::class, AccessControlChecker::class)
            ->setArguments([new Reference('security.access_listener'), new Reference('http_kernel')]);
        $container->setAlias(AccessCheckerInterface::class, AccessControlChecker::class);
        $container->register('router', stdClass::class)
            ->setPublic(true)
            ->setArguments([
                new Reference(AccessCheckerInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ]);

        return $container;
    }

    public function testProcessRemovesTheAccessCheckerWithoutFirewall(): void
    {
        $container = $this->buildContainerWithAccessChecker();

        $this->buildCompilerPass()->process($container);

        $this->assertFalse($container->hasDefinition(AccessControlChecker::class));
        $this->assertFalse($container->hasAlias(AccessCheckerInterface::class));

        // The router is built without access checker
        $container->compile();
        $this->assertSame([null], $container->getDefinition('router')->getArguments());
    }

    public function testProcessKeepsTheAccessCheckerWithFirewall(): void
    {
        $container = $this->buildContainerWithAccessChecker();
        $container->register('security.access_listener', stdClass::class);

        $this->buildCompilerPass()->process($container);

        $this->assertTrue($container->hasDefinition(AccessControlChecker::class));
        $this->assertTrue($container->hasAlias(AccessCheckerInterface::class));
    }

    public function testProcessKeepsAnotherAccessCheckerWithoutFirewall(): void
    {
        $container = $this->buildContainerWithAccessChecker();
        $container->register('app.access_checker', stdClass::class);
        $container->setAlias(AccessCheckerInterface::class, 'app.access_checker');

        $this->buildCompilerPass()->process($container);

        $this->assertFalse($container->hasDefinition(AccessControlChecker::class));
        $this->assertTrue($container->hasAlias(AccessCheckerInterface::class));
        $this->assertSame('app.access_checker', (string) $container->getAlias(AccessCheckerInterface::class));
    }
}
