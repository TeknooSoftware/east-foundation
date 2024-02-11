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

namespace Teknoo\East\Foundation\Normalizer\Object;

use function array_flip;
use function array_intersect_key;
use function array_merge;
use function array_values;
use function array_walk;
use function is_callable;

/**
 * Trait to filter data to export according to a group of keys
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait GroupsTrait
{
    /**
     * @var array<string, string[]>
     */
    protected array $groupsConfigurations = [];

    /**
     * @param array<string, string[]> $configurations
     */
    protected function setGroupsConfiguration(array $configurations): void
    {
        $final = [];
        foreach ($configurations as $key => &$groupsList) {
            foreach ($groupsList as &$groupName) {
                $final[$groupName][] = $key;
            }
        }

        $this->groupsConfigurations = $final;
    }

    /**
     * @param string[] $groups
     * @return array<string, mixed>
     */
    private function getAttributesForGroups(array &$groups): array
    {
        $selectedKeys = array_values(
            array_intersect_key(
                $this->groupsConfigurations,
                array_flip($groups),
            )
        );

        return array_flip(
            array_merge(...$selectedKeys)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param string[] $groups
     * @return array<string, mixed>
     */
    protected function filterExport(
        array &$data,
        array $groups,
        bool $lazyData = false,
    ): array {
        $dataFiltered = array_intersect_key(
            $data,
            $this->getAttributesForGroups($groups),
        );

        if ($lazyData) {
            array_walk(
                $dataFiltered,
                static function (mixed &$item): void {
                    if (is_callable($item)) {
                        $item = $item();
                    }
                }
            );
        }

        return $dataFiltered;
    }
}
