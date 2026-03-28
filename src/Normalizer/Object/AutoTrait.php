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

namespace Teknoo\East\Foundation\Normalizer\Object;

use ReflectionAttribute;
use ReflectionClass;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;

use function array_keys;
use function is_string;
use function method_exists;

/**
 * Trait to implement automatically Normalizable interface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin NormalizableInterface
 */
trait AutoTrait
{
    use GroupsTrait;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [];

    /**
     * @var array<string, array{name: string, loader: callable|string|null}>
     */
    private static array $exportMappings = [];

    /**
     * @return array<string, string[]>
     */
    private function getExportConfigurations(): array
    {
        if (!empty(self::$exportConfigurations)) {
            return self::$exportConfigurations;
        }

        $reflectionClass = new ReflectionClass(self::class);

        $classGroups = ['default'];
        $attributes = $reflectionClass->getAttributes(ClassGroup::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            $classGroups = $attribute->newInstance()->classGroups;
        }

        $configurations = [
            '@class' => $classGroups,
        ];

        $mappings = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes(Normalize::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $attrInstance = $attribute->newInstance();
                $name = $attrInstance->name ?? $reflectionProperty->getName();

                $loader = $attrInstance->loader;
                $propertyName = $reflectionProperty->getName();
                if (is_string($loader)) {
                    if (method_exists($this, $loader)) {
                        $loader = $this->{$loader}(...);
                    }

                    if ('@lazy' === $loader) {
                        $loader = static function (self $that) use ($propertyName) {
                            return $that->{$propertyName};
                        };
                    }
                }

                $configurations[$name] = $attrInstance->groups;
                $mappings[$name] = [
                    'name' => $propertyName,
                    'loader' => $loader,
                ];
            }
        }

        self::$exportMappings = $mappings;
        return self::$exportConfigurations = $configurations;
    }

    private function initializeGroupsConfiguration(): void
    {
        if (!empty($this->groupsConfigurations)) {
            return;
        }

        $this->setGroupsConfiguration($this->getExportConfigurations());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDataToExport(): array
    {
        $final = [];

        foreach (array_keys(self::$exportConfigurations) as $propertyName) {
            if ('@class' === $propertyName) {
                $final[$propertyName] = self::class;

                continue;
            }

            $final[$propertyName] = self::$exportMappings[$propertyName]['loader'] ??
                $this->{self::$exportMappings[$propertyName]['name']};
        }

        return $final;
    }

    /**
     * @param array<string, string[]> $context
     */
    public function exportToMeData(
        EastNormalizerInterface $normalizer,
        array $context = [],
    ): NormalizableInterface {
        $this->initializeGroupsConfiguration();
        $data = $this->buildDataToExport();

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
                lazyData: true,
            )
        );

        return $this;
    }
}
