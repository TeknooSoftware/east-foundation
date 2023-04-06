<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\DependencyInjection;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationExtension;

/**
 * Class EastFoundationExtensionTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\FoundationBundle\DependencyInjection\EastFoundationExtension
 */
class EastFrameworkExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

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

    public function testLoad()
    {
        self::assertInstanceOf(
            $this->getExtensionClass(),
            $this->buildExtension()->load([], $this->getContainerBuilderMock())
        );
    }

    public function testLoadErrorContainer()
    {
        $this->expectException(\TypeError::class);
        $this->buildExtension()->load([], new \stdClass());
    }

    public function testLoadErrorConfig()
    {
        $this->expectException(\TypeError::class);
        $this->buildExtension()->load(new \stdClass(), $this->getContainerBuilderMock());
    }
}
