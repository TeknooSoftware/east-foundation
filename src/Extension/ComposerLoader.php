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

use Composer\Autoload\ClassLoader;

use function array_filter;
use function array_flip;
use function array_keys;
use function array_merge;
use function class_exists;
use function is_a;

use const ARRAY_FILTER_USE_KEY;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ComposerLoader implements LoaderInterface
{
    /**
     * @var array<class-string<ExtensionInterface>>
     */
    private ?array $classes = null;

    /**
     * @return array<int, class-string<ExtensionInterface>>
     */
    private function findClasses(): array
    {
        if (!class_exists(ClassLoader::class)) {
            throw new \RuntimeException('This loader require the composer autoloader'); //todo
        }

        $final = [];
        $registeredLoaders = ClassLoader::getRegisteredLoaders();

        /** @var ClassLoader $classLoader */
        foreach ($registeredLoaders as $classLoader) {
            $final[] = array_filter(
                $classLoader->getClassMap(),
                static fn (string $class) => is_a($class, ExtensionInterface::class, true),
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
