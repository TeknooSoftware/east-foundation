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

namespace Teknoo\Tests\East\Framework\Listener;

use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Framework\Listener\KernelListener;
use Teknoo\East\Framework\Manager\ManagerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Zend\Diactoros\ServerRequest;

/**
 * Class KernelListenerTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
    private function getManagerMock()
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
    private function getHttpFoundationFactoryMock()
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
     * @return DiactorosFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDiactorosFactoryMock()
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
        $request = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', [], [], '', false);
        $request->expects($this->any())->method('getRequest')->willReturn(new Request());

        $this->getDiactorosFactoryMock()
            ->expects($this->any())
            ->method('createRequest')
            ->willReturn(new ServerRequest());

        $this->assertInstanceOf(
            $this->getKernelListenerClass(),
            $this->buildKernelListener()->onKernelRequest(
                $request
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