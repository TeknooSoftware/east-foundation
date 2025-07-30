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

namespace Teknoo\Tests\East\FoundationBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationExtension;

/**
 * Class EastFoundationExtensionTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EastFoundationExtension::class)]
class EastFrameworkExtensionTest extends TestCase
{
    private ?ContainerBuilder $container = null;

    private function getContainerBuilderMock(): ContainerBuilder&MockObject
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(ContainerBuilder::class);
        }

        return $this->container;
    }

    /**
     * @return EastFoundationExtension
     */
    private function buildExtension(): EastFoundationExtension
    {
        return new EastFoundationExtension();
    }

    private function getExtensionClass(): string
    {
        return EastFoundationExtension::class;
    }

    public function testLoad(): void
    {
        $this->assertInstanceOf(
            $this->getExtensionClass(),
            $this->buildExtension()->load([], $this->getContainerBuilderMock())
        );
    }

    public function testLoadErrorContainer(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExtension()->load([], new stdClass());
    }

    public function testLoadErrorConfig(): void
    {
        $this->expectException(TypeError::class);
        $this->buildExtension()->load(new stdClass(), $this->getContainerBuilderMock());
    }
}
