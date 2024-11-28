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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Extension;

use JsonException;
use Teknoo\East\Foundation\Extension\Exception\LoaderException;

use function array_keys;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function is_a;
use function is_array;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Extesion loader from a json file referencing all enabled extensions. The file is by default
 * `extensions/enabled.json`, it must be accessible from the workdir of your application.
 * The file must contains a json array of full qualified class name of extension to load.
 * All extensions must implements the interface `ExtensionInterface`
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FileLoader implements LoaderInterface
{
    private const ENV_FILE_NAME = 'TEKNOO_EAST_EXTENSION_FILE';

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
        $file = $_ENV[self::ENV_FILE_NAME] ?? self::DEFAULT_FILE_NAME;

        if (!file_exists($file)) {
            return [];
        }

        $extensionsJson = file_get_contents($file);
        if (empty($extensionsJson)) {
            throw new LoaderException("The extensions file '$file' is empty");
        }

        try {
            $extensionsClasses = json_decode(json: $extensionsJson, associative: true, flags: JSON_THROW_ON_ERROR);
            if (empty($extensionsClasses)) {
                return [];
            }
        } catch (JsonException $e) {
            throw new LoaderException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_array($extensionsClasses)) {
            throw new LoaderException(
                "The extensions file '$file' must contains an array of class name of extension to load"
            );
        }

        $final = [];
        foreach ($extensionsClasses as $class) {
            if (
                !is_string($class)
                || !class_exists($class, true)
                || !is_a($class, ExtensionInterface::class, true)
            ) {
                throw new LoaderException(
                    "The class $class must implements the interface " . ExtensionInterface::class
                );
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
