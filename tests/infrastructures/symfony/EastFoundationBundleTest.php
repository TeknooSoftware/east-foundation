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

namespace Teknoo\Tests\East\FoundationBundle;

use PHPUnit\Framework\TestCase;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EastFoundationBundleTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EastFoundationBundle::class)]
class EastFoundationBundleTest extends TestCase
{
    private function buildBundle(): EastFoundationBundle
    {
        return new EastFoundationBundle();
    }

    private function getBundleClass(): string
    {
        return EastFoundationBundle::class;
    }

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            $this->getBundleClass(),
            $this->buildBundle()->build(
                $this->createMock(ContainerBuilder::class)
            )
        );
    }

    public function testBuildErrorContainer(): void
    {
        $this->expectException(TypeError::class);
        $this->buildBundle()->build(new stdClass());
    }
}
