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

use function class_exists;
use function is_a;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Manager implements ManagerInterface
{
    private const KEY_ENV_NAME = 'TEKNOO_EAST_EXTENSION_LOADER';

    private static ?self $instance = null;

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

        if (empty($_ENV[self::KEY_ENV_NAME])) {
            $this->loader = new ComposerLoader();

            return;
        }

        $loaderClass = $_ENV[self::KEY_ENV_NAME];
        if (!class_exists(class: $loaderClass, autoload: true)) {
            throw new \RuntimeException('todo');
        }

        if (!is_a($loaderClass, LoaderInterface::class, true)) {
            throw new \RuntimeException('todo');
        }

        $this->loader = new $loaderClass();
    }

    final public static function run(): ManagerInterface
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
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
        foreach ($this->forExtensions() as $extension) {
            $extension->executeFor($module);
        }

        return $this;
    }
}
