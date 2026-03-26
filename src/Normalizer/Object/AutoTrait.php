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
     * @return array<string, string[]>
     */
    private static function getExportConfigurations(): array
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

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes(Group::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $configurations[$reflectionProperty->getName()] = $attribute->newInstance()->groups;
            }
        }

        return self::$exportConfigurations = $configurations;
    }

    private function initializeGroupsConfiguration(): void
    {
        if (!empty($this->groupsConfigurations)) {
            return;
        }

        $this->setGroupsConfiguration(self::getExportConfigurations());
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

            $final[$propertyName] = $this->{$propertyName};
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
