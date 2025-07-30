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

namespace Teknoo\Tests\East\Foundation\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Extension\Exception\LoaderException;
use Teknoo\East\Foundation\Extension\FileLoader;
use Teknoo\Tests\East\Foundation\Extension\Support\ExtensionMock2;

use function iterator_to_array;

/**
 * Class RecipeEndPointTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FileLoader::class)]
class FileLoaderTest extends TestCase
{
    private ?string $previousEnvValue = null;

    protected function setUp(): void
    {
        $this->previousEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? null;

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if ($this->previousEnvValue) {
            $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = $this->previousEnvValue;
        } elseif (isset($_ENV['TEKNOO_EAST_EXTENSION_FILE'])) {
            unset($_ENV['TEKNOO_EAST_EXTENSION_FILE']);
        }

        parent::tearDown();
    }

    public function testInvokeExceptionWhenFileNotFound(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'not-found';
        $this->assertEmpty(iterator_to_array((new FileLoader())()));
    }

    public function testInvokeExceptionWhenFileEmpty(): void
    {
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = __DIR__ . '/Support/emptyfile';
        iterator_to_array((new FileLoader())());
    }

    public function testInvokeExceptionWhenFileNotValidJson(): void
    {
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = __DIR__ . '/Support/invalid.json';
        iterator_to_array((new FileLoader())());
    }

    public function testInvokeExceptionWhenFileNotArrayJson(): void
    {
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = __DIR__ . '/Support/string.json';
        iterator_to_array((new FileLoader())());
    }

    public function testInvokeReferencedClassIsNotExtension(): void
    {
        $this->expectException(LoaderException::class);
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = __DIR__ . '/Support/badclass.json';
        iterator_to_array((new FileLoader())());
    }

    public function testInvoke(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = __DIR__ . '/Support/good.json';
        $loader = new FileLoader();

        $this->assertEquals(
            [
                ExtensionMock2::class
            ],
            iterator_to_array($loader())
        );

        $this->assertEquals(
            [
                ExtensionMock2::class
            ],
            iterator_to_array($loader())
        );
    }
}
