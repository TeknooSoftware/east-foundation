<?php

/*
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

namespace Teknoo\East\Foundation\Extension;

use Teknoo\East\Foundation\Extension\Exception\LoaderException;

use function class_exists;
use function is_a;

/**
 * Default manager extension. It ables to return ots singleton when its static method 'run' is called.
 * A singleton is provided because when extension are used because containers DI are often not available.
 *
 * By default, the manager is the `FileLoader` class to find extensions class to load. The loader
 * can be set thanks to the env variables `TEKNOO_EAST_EXTENSION_LOADER`. Or the loader can be injected at construction
 *
 * When its method `execute()` is called by modules. It will be forwarded to extensions to allow them to extend
 * the application's capacity like reference new bundles, update the container di, add some routes, etc...
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Manager implements ManagerInterface
{
    private const KEY_LOADER_ENV_NAME = 'TEKNOO_EAST_EXTENSION_LOADER';

    private const KEY_DISABLED_ENV_NAME = 'TEKNOO_EAST_EXTENSION_DISABLED';

    protected static ?self $instance = null;

    private readonly LoaderInterface $loader;

    /**
     * @var iterable<ExtensionInterface>
     */
    private ?iterable $extensions = null;

    public function __construct(?LoaderInterface $loader = null)
    {
        if ($loader) {
            $this->loader = $loader;

            return;
        }

        if (empty($_ENV[self::KEY_LOADER_ENV_NAME])) {
            $this->loader = new FileLoader();

            return;
        }

        $loaderClass = $_ENV[self::KEY_LOADER_ENV_NAME];
        if (!class_exists(class: $loaderClass, autoload: true)) {
            throw new LoaderException(
                "The extension loader class `$loaderClass` is not available"
            );
        }

        if (!is_a($loaderClass, LoaderInterface::class, true)) {
            throw new LoaderException(
                "The extension loader class `$loaderClass` must implements " . LoaderInterface::class
            );
        }

        $this->loader = new $loaderClass();
    }

    final public static function run(?LoaderInterface $loader = null): ManagerInterface
    {
        if (null === self::$instance) {
            self::$instance = new static($loader);
        }

        return self::$instance;
    }

    final public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @return iterable<ExtensionInterface>
     */
    private function forExtensions(): iterable
    {
        if (null === $this->extensions) {
            $this->extensions = [];
            foreach (($this->loader)() as $class) {
                $this->extensions[$class] = $class::create();
            }
        }

        yield from $this->extensions;
    }

    public function execute(ModuleInterface $module): ManagerInterface
    {
        if (empty($_ENV[self::KEY_DISABLED_ENV_NAME])) {
            foreach ($this->forExtensions() as $extension) {
                $extension->executeFor($module);
            }
        }

        return $this;
    }

    /**
     * @return iterable<class-string<ExtensionInterface>, string>
     */
    public function listLoadedExtensions(): iterable
    {
        if (empty($_ENV[self::KEY_DISABLED_ENV_NAME])) {
            foreach ($this->forExtensions() as $extension) {
                yield $extension::class => (string) $extension;
            }
        }

        return $this;
    }
}
