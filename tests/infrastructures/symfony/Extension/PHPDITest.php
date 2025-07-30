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

namespace Teknoo\Tests\East\FoundationBundle\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;
use Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\FoundationBundle\Extension\Exception\MissingBuilderException;
use Teknoo\East\FoundationBundle\Extension\PHPDI;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PHPDI::class)]
class PHPDITest extends TestCase
{
    public function testCreate(): void
    {
        $module1 = PHPDI::create();
        $module2 = PHPDI::create();

        $this->assertSame($module1, $module2);
    }

    public function testConfigure(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);

        $this->assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testPrepareCompilation(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertInstanceOf(PHPDI::class, $module);

                    $module->prepareCompilation('foo');
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('prepareCompilation')
            ->with('foo');

        $this->assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testPrepareCompilationWithoutBuider(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->prepareCompilation('foo');
    }

    public function testEnableCache(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertInstanceOf(PHPDI::class, $module);

                    $module->enableCache(true);
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('enableCache')
            ->with(true);

        $this->assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testEnableCacheWithoutBuider(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->enableCache(true);
    }

    public function testLoadDefinition(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertInstanceOf(PHPDI::class, $module);

                    $module->loadDefinition(['foo']);
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('loadDefinition')
            ->with(['foo']);

        $this->assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testLoadDefinitionWithoutBuider(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->loadDefinition(['foo']);
    }

    public function testImport(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertInstanceOf(PHPDI::class, $module);

                    $module->import('foo', 'bar');
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('import')
            ->with('foo', 'bar');

        $this->assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testImportWithoutBuider(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->import('foo', 'bar');
    }
}
