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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Extension\ComposerLoader;
use Teknoo\East\Foundation\Extension\Exception\LoaderException;
use Teknoo\East\Foundation\Extension\LoaderInterface;
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ModuleInterface;
use Teknoo\Tests\East\Foundation\Extension\Support\ExtensionMock1;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Manager::class)]
#[CoversClass(ExtensionMock1::class)]
class ManagerTest extends TestCase
{
    private ?string $previousLoaderEnvValue = null;
    private ?string $previousDisabledEnvValue = null;

    protected function setUp(): void
    {
        $this->previousLoaderEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] ?? null;
        $this->previousDisabledEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] ?? null;

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if ($this->previousLoaderEnvValue) {
            $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = $this->previousLoaderEnvValue;
        } elseif (isset($_ENV['TEKNOO_EAST_EXTENSION_LOADER'])) {
            unset($_ENV['TEKNOO_EAST_EXTENSION_LOADER']);
        }

        if ($this->previousDisabledEnvValue) {
            $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = $this->previousDisabledEnvValue;
        } elseif (isset($_ENV['TEKNOO_EAST_EXTENSION_DISABLED'])) {
            unset($_ENV['TEKNOO_EAST_EXTENSION_DISABLED']);
        }

        parent::tearDown();
    }

    public function testRunWithLoader()
    {
        $loader = $this->createMock(LoaderInterface::class);
        Manager::reset();
        $m1 = Manager::run($loader);
        $m2 = Manager::run($loader);

        self::assertInstanceOf(Manager::class, $m1);
        self::assertSame($m1, $m2);
    }

    public function testRunWithoutLoader()
    {
        Manager::reset();
        $m1 = Manager::run();
        $m2 = Manager::run();

        self::assertInstanceOf(Manager::class, $m1);
        self::assertSame($m1, $m2);
    }

    public function testRunWithLoaderClassInEnvReferencingUnknownClass()
    {
        Manager::reset();
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = 'foo';
        Manager::run();
    }

    public function testRunWithLoaderClassInEnvReferencingNotLoaderClass()
    {
        Manager::reset();
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = \stdClass::class;
        Manager::run();
    }

    public function testRunWithLoaderClassInEnv()
    {
        Manager::reset();
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = ComposerLoader::class;
        $m1 = Manager::run();
        $m2 = Manager::run();

        self::assertInstanceOf(Manager::class, $m1);
        self::assertSame($m1, $m2);
    }

    public function testExecute()
    {
        $loader = $this->createMock(LoaderInterface::class);
        Manager::reset();
        $m1 = Manager::run($loader);
        $m2 = Manager::run($loader);

        $loader->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                ExtensionMock1::class
            ]);

        $ext = ExtensionMock1::create();

        $module = $this->createMock(ModuleInterface::class);
        $m1->execute($module);

        self::assertSame($module, $ext->module);
        $ext->module = null;

        $m2->execute($module);

        self::assertSame($module, $ext->module);
        $ext->module = null;
    }

    public function testExecuteDisabled()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = 'true';

        Manager::reset();
        $m1 = Manager::run($loader);
        $m2 = Manager::run($loader);

        $loader->expects($this->never())
            ->method('__invoke')
            ->willReturn([
                ExtensionMock1::class
            ]);

        $ext = ExtensionMock1::create();

        $module = $this->createMock(ModuleInterface::class);
        $m1->execute($module);
        self::assertNull($ext->module);

        $m2->execute($module);
        self::assertNull($ext->module);
    }

    public function testListLoadedExtensions()
    {
        $loader = $this->createMock(LoaderInterface::class);
        Manager::reset();
        $m1 = Manager::run($loader);

        $loader->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                ExtensionMock1::class
            ]);

        self::assertEquals(
            [
                ExtensionMock1::class => 'test 1'
            ],
            \iterator_to_array($m1->listLoadedExtensions())
        );
    }

    public function testListLoadedExtensionsDisabled()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = 'true';

        Manager::reset();
        $m1 = Manager::run($loader);

        $loader->expects($this->never())
            ->method('__invoke')
            ->willReturn([
                ExtensionMock1::class
            ]);

        self::assertEquals(
            [],
            \iterator_to_array($m1->listLoadedExtensions())
        );
    }
}
