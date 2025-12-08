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

namespace Teknoo\Tests\East\FoundationBundle\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\FoundationBundle\Extension\Routes;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Routes::class)]
class RoutesTest extends TestCase
{
    public function testExtendsBundles(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertinstanceOf(Routes::class, $module);

                    $this->assertEquals('test', $module->getEnvironment());
                    $module->import('foo', 'bar', false, 'test');

                    return $manager;
                }
            );

        $configurator = new RoutingConfigurator(
            $this->createStub(RouteCollection::class),
            $this->createStub(PhpFileLoader::class),
            __DIR__,
            __FILE__,
        );

        Routes::extendsRoutes(
            $configurator,
            'test',
            $manager,
        );
    }
}
