<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
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

namespace Teknoo\Tests\East\FoundationBundle\Listener;

use PHPUnit\Framework\MockObject\MockObject;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

    private function getManagerMock(): ManagerInterface&MockObject
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    private function getClientWithResponseEventInterfaceMock(): ClientWithResponseEventInterface&MockObject
    {
        if (!$this->clientWithResponseEventInterface instanceof ClientWithResponseEventInterface) {
            $this->clientWithResponseEventInterface = $this->createMock(ClientWithResponseEventInterface::class);
        }

        return $this->clientWithResponseEventInterface;
    }

    private function getFactoryMock(): HttpMessageFactoryInterface&MockObject
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
            $this->getFactoryMock(),
        );
    }

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

        $this->getClientWithResponseEventInterfaceMock()
            ->expects(self::never())
            ->method('mustSendAResponse');

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
