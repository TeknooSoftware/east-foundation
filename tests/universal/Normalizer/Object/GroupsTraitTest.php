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

namespace Teknoo\Tests\East\Foundation\Normalizer\Object;

use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;

/** *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GroupsTraitTest extends TestCase
{
    public function buildObject(): object
    {
        return new class () {
            use GroupsTrait;

            public function setConfig(array $configurations): void
            {
                $this->setGroupsConfiguration($configurations);
            }

            public function runFilter(
                array $data,
                array $groups,
                bool $lazyData = false,
            ): array {
                return $this->filterExport($data, $groups, $lazyData);
            }
        };
    }

    public function testFilteringWithoutsGroupsDefined(): void
    {
        $object = $this->buildObject();

        $this->assertEquals(
            [],
            $object->runFilter(
                [
                    'key1' => 'foo',
                    'key2' => 123,
                    'key3' => ['foo', 'bar'],
                    'key4' => new stdClass(),
                ],
                [
                    'group1'
                ]
            ),
        );
    }

    public function testFilteringWithGroupNotFound(): void
    {
        $object = $this->buildObject();

        $object->setConfig(
            [
                'key1' => ['group2', 'group3'],
                'key2' => ['group3'],
                'key3' => ['group2'],
            ]
        );

        $this->assertEquals(
            [],
            $object->runFilter(
                [
                    'key1' => 'foo',
                    'key2' => 123,
                    'key3' => ['foo', 'bar'],
                    'key4' => new stdClass(),
                ],
                [
                    'group1'
                ]
            ),
        );
    }

    public function testFilteringWithGroups(): void
    {

        $object = $this->buildObject();

        $object->setConfig(
            [
                'key1' => ['group2', 'group3'],
                'key2' => ['group3'],
                'key3' => ['group2'],
                'key5' => ['group4'],
            ]
        );

        $this->assertEquals(
            [
                'key1' => 'foo',
                'key2' => 123,
                'key3' => ['foo', 'bar'],
            ],
            $object->runFilter(
                [
                    'key1' => 'foo',
                    'key2' => 123,
                    'key3' => ['foo', 'bar'],
                    'key4' => new stdClass(),
                    'key5' => 'bar',
                ],
                [
                    'group1',
                    'group3',
                    'group2',
                ]
            ),
        );
    }

    public function testFilteringWithGroupsAndLasyData(): void
    {

        $object = $this->buildObject();

        $object->setConfig(
            [
                'key1' => ['group2', 'group3'],
                'key2' => ['group3'],
                'key3' => ['group2'],
                'key5' => ['group4'],
            ]
        );

        $this->assertEquals(
            [
                'key1' => 'foo',
                'key2' => 123,
                'key3' => ['foo', 'bar'],
            ],
            $object->runFilter(
                [
                    'key1' => fn (): string => 'foo',
                    'key2' => 123,
                    'key3' => fn (): array => ['foo', 'bar'],
                    'key4' => fn (): \stdClass => new stdClass(),
                    'key5' => fn (): string => 'bar',
                ],
                [
                    'group1',
                    'group3',
                    'group2',
                ],
                true,
            ),
        );
    }

    public function testFilteringWithGroupsWithClosureButNotLazyData(): void
    {
        $toCall1 = fn (): string => 'foo';
        $toCall2 = fn (): array => ['foo', 'bar'];

        $object = $this->buildObject();

        $object->setConfig(
            [
                'key1' => ['group2', 'group3'],
                'key2' => ['group3'],
                'key3' => ['group2'],
                'key5' => ['group4'],
            ]
        );

        $this->assertEquals(
            [
                'key1' => $toCall1,
                'key2' => 123,
                'key3' => $toCall2,
            ],
            $object->runFilter(
                [
                    'key1' => $toCall1,
                    'key2' => 123,
                    'key3' => $toCall2,
                    'key4' => fn (): \stdClass => new stdClass(),
                    'key5' => fn (): string => 'bar',
                ],
                [
                    'group1',
                    'group3',
                    'group2',
                ],
                false,
            ),
        );
    }
}
