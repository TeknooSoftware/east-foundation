<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle;

use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EastFoundationBundleTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\EastFoundationBundle
 */
class EastFoundationBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return EastFoundationBundle
     */
    private function buildBundle(): EastFoundationBundle
    {
        return new EastFoundationBundle();
    }

    private function getBundleClass(): string
    {
        return EastFoundationBundle::class;
    }

    public function testBuild()
    {
        self::assertInstanceOf(
            $this->getBundleClass(),
            $this->buildBundle()->build(
                $this->createMock(ContainerBuilder::class)
            )
        );
    }

    public function testBuildErrorContainer()
    {
        $this->expectException(\TypeError::class);
        $this->buildBundle()->build(new \stdClass());
    }
}
