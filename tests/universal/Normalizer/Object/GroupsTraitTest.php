<?php
/**
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

namespace Teknoo\Tests\East\Foundation\Normalizer\Object;

use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;

/** *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GroupsTraitTest extends TestCase
{
    public function buildObject(): object
    {
        return new class {
            use GroupsTrait;

            public function setConfig(array $configurations): void
            {
                $this->setGroupsConfiguration($configurations);
            }

            public function runFilter(
                array $data,
                array $groups,
                bool $lazyData = false,
            ) {
                return $this->filterExport($data, $groups, $lazyData);
            }
        };
    }

    public function testFilteringWithoutsGroupsDefined()
    {
        $object = $this->buildObject();

        self::assertEquals(
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

    public function testFilteringWithGroupNotFound()
    {
        $object = $this->buildObject();

        $object->setConfig(
            [
                'key1' => ['group2', 'group3'],
                'key2' => ['group3'],
                'key3' => ['group2'],
            ]
        );

        self::assertEquals(
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

    public function testFilteringWithGroups()
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

        self::assertEquals(
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

    public function testFilteringWithGroupsAndLasyData()
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

        self::assertEquals(
            [
                'key1' => 'foo',
                'key2' => 123,
                'key3' => ['foo', 'bar'],
            ],
            $object->runFilter(
                [
                    'key1' => fn () => 'foo',
                    'key2' => 123,
                    'key3' => fn () => ['foo', 'bar'],
                    'key4' => fn () => new stdClass(),
                    'key5' => fn () => 'bar',
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

    public function testFilteringWithGroupsWithClosureButNotLazyData()
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

        self::assertEquals(
            [
                'key1' => fn () => 'foo',
                'key2' => 123,
                'key3' => fn () => ['foo', 'bar'],
            ],
            $object->runFilter(
                [
                    'key1' => fn () => 'foo',
                    'key2' => 123,
                    'key3' => fn () => ['foo', 'bar'],
                    'key4' => fn () => new stdClass(),
                    'key5' => fn () => 'bar',
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
