<?php

/*
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

namespace Teknoo\East\Foundation\Extension;

use Composer\Autoload\ClassLoader;
use Teknoo\East\Foundation\Extension\Exception\LoaderException;
use Throwable;

use function array_filter;
use function array_keys;
use function array_merge;
use function class_exists;
use function is_a;
use function preg_match;
use function preg_quote;

use const ARRAY_FILTER_USE_KEY;

/**
 * Extension class loader to detect all extensions from the Composer autoloaded.
 * This loader can be slow because it will check all referenced class from the composer autoloader
 * and return classes implementing the interface `ExtensionInterface`.
 * There are no option to disable an extension loaded by Composer
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ComposerLoader implements LoaderInterface
{
    /**
     * @var array<class-string<ExtensionInterface>>
     */
    private ?array $classes = null;

    /**
     * @var string[]
     */
    private static array $ignoreClass = [
        'Behat',
        'Symfony',
    ];

    /**
     * @return array<int, class-string<ExtensionInterface>>
     */
    private function findClasses(): array
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(ClassLoader::class)) {
            throw new LoaderException('This loader require the composer autoloader');
        }

        // @codeCoverageIgnoreEnd

        $final = [];
        $registeredLoaders = ClassLoader::getRegisteredLoaders();

        $pattern = '#' . implode('|', array_map(preg_quote(...), self::$ignoreClass)) . '#S';
        /** @var ClassLoader $classLoader */
        foreach ($registeredLoaders as $classLoader) {
            $final[] = array_filter(
                $classLoader->getClassMap(),
                static function (string $class) use ($pattern): bool {
                    try {
                        return !preg_match($pattern, $class, $m)
                            && $class !== ExtensionInterface::class
                            && class_exists($class, true)
                            && is_a($class, ExtensionInterface::class, true);
                    } catch (Throwable) {
                        return false;
                    }
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return array_keys(array_merge(...$final));
    }

    public function __invoke(): iterable
    {
        if (null === $this->classes) {
            $this->classes = $this->findClasses();
        }

        yield from $this->classes;
    }
}
