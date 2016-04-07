<?php

namespace Teknoo\Tests\East\Framework\Router;

use Teknoo\East\Framework\Router\Router;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Framework\Processor\ProcessorInterface;

/**
 * Class RouterTest
 * @package Teknoo\Tests\East\Framework\Router
 * @covers Teknoo\East\Framework\Router\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @return UrlMatcherInterface
     */
    private function getUrlMatcherMock(): UrlMatcherInterface
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->getMock(
                'Symfony\Component\Routing\Matcher\UrlMatcherInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->matcher;
    }

    /**
     * @return ProcessorInterface
     */
    private function getProcessorMock(): ProcessorInterface
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->getMock(
                'Teknoo\East\Framework\Processor\ProcessorInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->processor;
    }

    /**
     * @return Router
     */
    private function buildRouter(): Router
    {
        return new Router($this->getUrlMatcherMock(), $this->getProcessorMock());
    }
}