<?php

namespace Teknoo\Tests\East\Framework\Listener;

use Teknoo\East\Framework\Listener\KernelListener;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

/**
 * Class KernelListenerTest
 * @package Teknoo\Tests\East\Framework\Listener
 * @covers Teknoo\East\Framework\Listener\KernelListener
 */
class KernelListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @return ManagerInterface
     */
    private function getManagerMock(): ManagerInterface
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->getMock(
                'Teknoo\East\Framework\Manager\ManagerInterface',
                [],
                [],
                '',
                false
            );
        }

        return $this->manager;
    }

    /**
     * @return HttpFoundationFactory
     */
    private function getHttpFoundationFactoryMock(): HttpFoundationFactory
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->getMock(
                'Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory',
                [],
                [],
                '',
                false
            );
        }

        return $this->httpFoundationFactory;
    }

    /**
     * @return DiactorosFactory
     */
    private function getDiactorosFactoryMock(): DiactorosFactory
    {
        if (!$this->diactorosFactory instanceof DiactorosFactory) {
            $this->diactorosFactory = $this->getMock(
                'Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory',
                [],
                [],
                '',
                false
            );
        }

        return $this->diactorosFactory;
    }

    /**
     * @return KernelListener
     */
    private function buildKernelListener(): KernelListener
    {
        return new KernelListener(
            $this->getManagerMock(),
            $this->getHttpFoundationFactoryMock(),
            $this->getDiactorosFactoryMock()
        );
    }

    /**
     * @return string
     */
    private function getKernelListenerClass(): string
    {
        return 'Teknoo\East\Framework\Listener\KernelListener';
    }

    public function testOnKernelRequest()
    {
        $this->assertInstanceOf(
            $this->getKernelListenerClass(),
            $this->buildKernelListener()->onKernelRequest(
                $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', [], [], '', false)
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testOnKernelRequestError()
    {
        $this->buildKernelListener()->onKernelRequest(new \stdClass());
    }
}