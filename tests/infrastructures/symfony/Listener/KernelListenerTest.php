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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\Listener;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Teknoo\East\FoundationBundle\Http\ClientWithResponseEventInterface;
use Teknoo\East\FoundationBundle\Listener\KernelListener;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

/**
 * Class KernelListenerTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Listener\KernelListener
 */
class KernelListenerTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var ClientWithResponseEventInterface
     */
    private $clientWithResponseEventInterface;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $factory;

    /**
     * @return ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getManagerMock()
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    /**
     * @return ClientWithResponseEventInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getClientWithResponseEventInterfaceMock()
    {
        if (!$this->clientWithResponseEventInterface instanceof ClientWithResponseEventInterface) {
            $this->clientWithResponseEventInterface = $this->createMock(ClientWithResponseEventInterface::class);
        }

        return $this->clientWithResponseEventInterface;
    }

    /**
     * @return HttpMessageFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getFactoryMock()
    {
        if (!$this->factory instanceof HttpMessageFactoryInterface) {
            $this->factory = $this->createMock(HttpMessageFactoryInterface::class);
        }

        return $this->factory;
    }

    /**
     * @return KernelListener
     */
    private function buildKernelListener(): KernelListener
    {
        return new KernelListener(
            $this->getManagerMock(),
            $this->getClientWithResponseEventInterfaceMock(),
            $this->getFactoryMock()
        );
    }

    /**
     * @return string
     */
    private function getKernelListenerClass(): string
    {
        return KernelListener::class;
    }

    public function testOnKernelRequest()
    {
        $request = $this->createMock(RequestEvent::class);
        $request->expects(self::any())->method('getRequest')->willReturn(new Request());

        $psrRquest = $this->createMock(ServerRequestInterface::class);
        $psrRquest->expects(self::any())->method('withAttribute')->willReturnSelf();

        $this->getFactoryMock()
            ->expects(self::any())
            ->method('createRequest')
            ->willReturn($psrRquest);

        $this->getClientWithResponseEventInterfaceMock()
            ->expects(self::once())
            ->method('setRequestEvent')
            ->with($request)
            ->willReturnSelf();

        self::assertInstanceOf(
            $this->getKernelListenerClass(),
            $this->buildKernelListener()->onKernelRequest(
                $request
            )
        );
    }

    public function testOnKernelRequestErrorLoopFromSymfony()
    {
        $symfonyRequest = new Request();
        $symfonyRequest->attributes->set('exception', new \Exception());
        $request = $this->createMock(RequestEvent::class);
        $request->expects(self::any())->method('getRequest')->willReturn($symfonyRequest);

        $this->getFactoryMock()
            ->expects(self::any())
            ->method('createRequest')
            ->willReturn($this->createMock(ServerRequestInterface::class));

        $this->getClientWithResponseEventInterfaceMock()
            ->expects(self::never())
            ->method('setRequestEvent');

        $this->getManagerMock()
            ->expects(self::never())
            ->method('receiveRequest');

        self::assertInstanceOf(
            $this->getKernelListenerClass(),
            $this->buildKernelListener()->onKernelRequest(
                $request
            )
        );
    }

    public function testOnKernelRequestError()
    {
        $this->expectException(\TypeError::class);
        $this->buildKernelListener()->onKernelRequest(new \stdClass());
    }
}
