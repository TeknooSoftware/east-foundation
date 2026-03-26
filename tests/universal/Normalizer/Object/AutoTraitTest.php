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

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\Tests\East\Support\Normalization\AutoTraitDefaultGroupFixture;
use Teknoo\Tests\East\Support\Normalization\AutoTraitMultipleGroupsFixture;
use Teknoo\Tests\East\Support\Normalization\AutoTraitPartialGroupFixture;
use Teknoo\Tests\East\Support\Normalization\AutoTraitWithClassGroupFixture;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(AutoTrait::class)]
#[CoversTrait(GroupsTrait::class)]
class AutoTraitTest extends TestCase
{
    public function testExportWithDefaultGroupWithoutContext(): void
    {
        $object = new AutoTraitDefaultGroupFixture('hello', 42);

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => AutoTraitDefaultGroupFixture::class,
                'name' => 'hello',
                'value' => 42,
            ]);

        $result = $object->exportToMeData($normalizer);

        $this->assertSame($object, $result);
    }

    public function testExportWithSpecificGroupInContext(): void
    {
        $object = new AutoTraitMultipleGroupsFixture('hello', 42, 'secret');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'name' => 'hello',
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['group1']]);
    }

    public function testExportWithTwoInstance(): void
    {
        $object1 = new AutoTraitMultipleGroupsFixture('hello', 42, 'secret');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'name' => 'hello',
            ]);

        $object1->exportToMeData($normalizer, ['groups' => ['group1']]);

        $object2 = new AutoTraitMultipleGroupsFixture('hello', 42, 'secret');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'value' => 42,
                'secret' => 'secret',
            ]);

        $object2->exportToMeData($normalizer, ['groups' => ['group2']]);
    }

    public function testExportWithMultipleCall(): void
    {
        $object = new AutoTraitMultipleGroupsFixture('hello', 42, 'secret');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'name' => 'hello',
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['group1']]);

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'value' => 42,
                'secret' => 'secret',
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['group2']]);
    }

    public function testExportWithMultipleGroupsInContext(): void
    {
        $object = new AutoTraitMultipleGroupsFixture('hello', 42, 'secret');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'name' => 'hello',
                'value' => 42,
                'secret' => 'secret',
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['group1', 'group2']]);
    }

    public function testExportWithClassGroupAttribute(): void
    {
        $object = new AutoTraitWithClassGroupFixture('hello', 42);

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => AutoTraitWithClassGroupFixture::class,
                'name' => 'hello',
                'value' => 42,
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['api']]);
    }

    public function testExportWithClassGroupNotMatchingContext(): void
    {
        $object = new AutoTraitWithClassGroupFixture('hello', 42);

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                'name' => 'hello',
                'value' => 42,
            ]);

        $object->exportToMeData($normalizer, ['groups' => ['default']]);
    }

    public function testPropertiesWithoutGroupAttributeAreExcluded(): void
    {
        $object = new AutoTraitPartialGroupFixture('hello', 42, 'excluded');

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => AutoTraitPartialGroupFixture::class,
                'name' => 'hello',
            ]);

        $object->exportToMeData($normalizer);
    }

    public function testExportReturnsSelf(): void
    {
        $object = new AutoTraitDefaultGroupFixture('hello', 42);

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData');

        $result = $object->exportToMeData($normalizer);

        $this->assertInstanceOf(NormalizableInterface::class, $result);
        $this->assertSame($object, $result);
    }
}
