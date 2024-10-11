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

namespace Teknoo\Tests\East\FoundationBundle\Extension;

use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\FoundationBundle\Extension\Bundles;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bundles::class)]
class BundlesTest extends TestCase
{
    public function testExtendsBundles()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager) {
                    self::assertinstanceOf(Bundles::class, $module);

                    $module->register(
                        DateTimeInterface::class,
                        ['test' => true]
                    );

                    return $manager;
                }
            );

        $bundles = [
            stdClass::class => ['dev' => true],
        ];

        Bundles::extendsBundles(
            $bundles,
            $manager,
        );

        self::assertEquals(
            [
                stdClass::class => ['dev' => true],
                DateTimeInterface::class => ['test' => true]
            ],
            $bundles
        );
    }
}
