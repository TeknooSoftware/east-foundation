<?php

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