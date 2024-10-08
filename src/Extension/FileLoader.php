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

use function array_keys;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function is_a;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FileLoader implements LoaderInterface
{
    private const DEFAULT_FILE_NAME = 'extensions/enabled.json';

    /**
     * @var array<class-string<ExtensionInterface>>
     */
    private ?array $classes = null;

    /**
     * @return array<int, class-string<ExtensionInterface>>
     */
    private function findClasses(): array
    {
        if (!file_exists(self::DEFAULT_FILE_NAME)) {
            return [];
        }

        $extensionsJson = file_get_contents(self::DEFAULT_FILE_NAME);
        if (empty($extensionsJson)) {
            throw new \RuntimeException('todo');
        }

        $extensionsClasses = json_decode(json: $extensionsJson, associative: true, flags: JSON_THROW_ON_ERROR);
        if (empty($extensionsClasses)) {
            return [];
        }

        if (!\is_array($extensionsClasses)) {
            throw new \RuntimeException('todo');
        }

        $final = [];
        foreach ($extensionsClasses as $class) {
            if (
                !is_string($class)
                || !class_exists($class, true)
                || !is_a($class, ExtensionInterface::class, true)
            ) {
                throw new \RuntimeException('todo');
            }

            $final[$class] = true;
        }

        return array_keys($final);
    }

    public function __invoke(): iterable
    {
        if (null === $this->classes) {
            $this->classes = $this->findClasses();
        }

        yield from $this->classes;
    }
}
