<?php

namespace Teknoo\Tests\East\Framework\Processor;

use Teknoo\East\Framework\Processor\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProcessorTest
 * @package Teknoo\Tests\East\Framework\Processor
 * @covers Teknoo\East\Framework\Processor\Processor
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
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
     * @return Processor
     */
    private function buildProcessor(): Processor
    {
        return new Processor($this->getContainerMock());
    }
}