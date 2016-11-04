<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle;

use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EastFoundationBundleTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\EastFoundationBundle
 */
class EastFoundationBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EastFoundationBundle
     */
    private function buildBundle(): EastFoundationBundle
    {
        return new EastFoundationBundle();
    }

    /**
     * @return string
     */
    private function getBundleClass(): string
    {
        return EastFoundationBundle::class;
    }

    public function testBuild()
    {
        $this->assertInstanceOf(
            $this->getBundleClass(),
            $this->buildBundle()->build(
                $this->createMock(ContainerBuilder::class)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testBuildErrorContainer()
    {
        $this->buildBundle()->build(new \stdClass());
    }
}
