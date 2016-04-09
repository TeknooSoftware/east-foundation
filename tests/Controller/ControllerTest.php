<?php
/**
 * East Framework.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Framework\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Teknoo\East\Framework\Controller\Controller;

/**
 * Class ControllerTest
 * @package Teknoo\Tests\East\Framework\Controller
 * @covers Teknoo\East\Framework\Controller\Controller
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface
     */
    private function getContainerMock(): ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->getMock(
                'Symfony\Component\DependencyInjection\ContainerInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->container;
    }

    /**
     * @return Controller
     */
    private function buildController(): Controller
    {
        return new class extends Controller{};
    }

    /**
     * @return string
     */
    private function getControllerClassName(): string
    {
        return 'Teknoo\East\Framework\Controller\Controller';
    }

    public function testSetContainer()
    {
        $this->assertInstanceOf(
            $this->getControllerClassName(),
            $this->buildController()->setContainer($this->getContainerMock())
        );
    }

    public function testSetContainerNull()
    {
        $this->assertInstanceOf(
            $this->getControllerClassName(),
            $this->buildController()->setContainer(null)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testSetContainerTypeError()
    {
        $this->buildController()->setContainer(new \stdClass());
    }
}