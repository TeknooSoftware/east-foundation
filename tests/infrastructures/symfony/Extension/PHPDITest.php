<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PHPDI::class)]
class PHPDITest extends TestCase
{
    public function testCreate()
    {
        $module1 = PHPDI::create();
        $module2 = PHPDI::create();

        self::assertSame($module1, $module2);
    }

    public function testConfigure()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);

        self::assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testPrepareCompilation()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager) {
                    self::assertInstanceOf(PHPDI::class, $module);

                    $module->prepareCompilation('foo');
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('prepareCompilation')
            ->with('foo');

        self::assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testPrepareCompilationWithoutBuider()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->prepareCompilation('foo');
    }

    public function testEnableCache()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager) {
                    self::assertInstanceOf(PHPDI::class, $module);

                    $module->enableCache(true);
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('enableCache')
            ->with(true);

        self::assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testEnableCacheWithoutBuider()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->enableCache(true);
    }

    public function testLoadDefinition()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager) {
                    self::assertInstanceOf(PHPDI::class, $module);

                    $module->loadDefinition(['foo']);
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('loadDefinition')
            ->with(['foo']);

        self::assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testLoadDefinitionWithoutBuider()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->loadDefinition(['foo']);
    }

    public function testImport()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager) {
                    self::assertInstanceOf(PHPDI::class, $module);

                    $module->import('foo', 'bar');
                    return $manager;
                }
            );

        $module = new PHPDI($manager);

        $builder = $this->createMock(BridgeBuilderInterface::class);
        $builder->expects($this->once())
            ->method('import')
            ->with('foo', 'bar');

        self::assertInstanceOf(
            ExtensionInterface::class,
            $module->configure($builder)
        );
    }

    public function testImportWithoutBuider()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $module = new PHPDI($manager);

        $this->expectException(MissingBuilderException::class);
        $module->import('foo', 'bar');
    }
}
