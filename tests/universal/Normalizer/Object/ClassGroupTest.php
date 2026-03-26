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

use Attribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ClassGroup::class)]
class ClassGroupTest extends TestCase
{
    public function testConstructWithNoGroups(): void
    {
        $classGroup = new ClassGroup();

        $this->assertSame([], $classGroup->classGroups);
    }

    public function testConstructWithSingleGroup(): void
    {
        $classGroup = new ClassGroup('group1');

        $this->assertSame(['group1'], $classGroup->classGroups);
    }

    public function testConstructWithMultipleGroups(): void
    {
        $classGroup = new ClassGroup('group1', 'group2', 'group3');

        $this->assertSame(['group1', 'group2', 'group3'], $classGroup->classGroups);
    }

    public function testAttributeTargetsClass(): void
    {
        $reflection = new ReflectionClass(ClassGroup::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attribute = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_CLASS, $attribute->flags);
    }
}
