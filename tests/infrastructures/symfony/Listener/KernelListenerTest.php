<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Listener;

use PHPUnit\Framework\MockObject\Stub;
use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(KernelListener::class)]
class KernelListenerTest extends TestCase
{
    private ?ManagerInterface $manager = null;

    private ?ClientWithResponseEventInterface $clientWithResponseEventInterface = null;

    private ?HttpMessageFactoryInterface $factory = null;

    private function getManagerMock(): ManagerInterface&MockObject
    {
        if (
            !$this->manager instanceof ManagerInterface
            || !$this->manager instanceof MockObject
        ) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    private function getManagerStub(): ManagerInterface&Stub
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createStub(ManagerInterface::class);
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

    private function getClientWithResponseEventInterfaceStub(): ClientWithResponseEventInterface&Stub
    {
        if (!$this->clientWithResponseEventInterface instanceof ClientWithResponseEventInterface) {
            $this->clientWithResponseEventInterface = $this->createStub(ClientWithResponseEventInterface::class);
        }

        return $this->clientWithResponseEventInterface;
    }

    private function getFactoryMock(): HttpMessageFactoryInterface&Stub
    {
        if (!$this->factory instanceof HttpMessageFactoryInterface) {
            $this->factory = $this->createStub(HttpMessageFactoryInterface::class);
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

    private function buildKernelListenerWithStubs(): KernelListener
    {
        return new KernelListener(
            $this->getManagerStub(),
            $this->getClientWithResponseEventInterfaceStub(),
            $this->getFactoryMock(),
        );
    }

    private function getKernelListenerClass(): string
    {
        return KernelListener::class;
    }

    public function testOnKernelRequest(): void
    {
        $request = $this->createStub(RequestEvent::class);
        $request->method('getRequest')->willReturn(new Request());

        $psrRquest = $this->createStub(ServerRequestInterface::class);
        $psrRquest->method('withAttribute')->willReturnSelf();

        $this->getFactoryMock()

            ->method('createRequest')
            ->willReturn($psrRquest);

        $this->getClientWithResponseEventInterfaceMock()
            ->expects($this->once())
            ->method('setRequestEvent')
            ->with($request)
            ->willReturnSelf();

        $this->getClientWithResponseEventInterfaceMock()
            ->expects($this->never())
            ->method('mustSendAResponse');

        $listener = new KernelListener(
            $this->createStub(ManagerInterface::class),
            $this->getClientWithResponseEventInterfaceMock(),
            $this->getFactoryMock(),
        );

        $this->assertInstanceOf(
            $this->getKernelListenerClass(),
            $listener->onKernelRequest(
                $request
            )
        );
    }

    public function testOnKernelRequestErrorLoopFromSymfony(): void
    {
        $symfonyRequest = new Request();
        $symfonyRequest->attributes->set('exception', new \Exception());
        $request = $this->createStub(RequestEvent::class);
        $request->method('getRequest')->willReturn($symfonyRequest);

        $this->getFactoryMock()
            ->method('createRequest')
            ->willReturn($this->createStub(ServerRequestInterface::class));

        $this->getClientWithResponseEventInterfaceMock()
            ->expects($this->never())
            ->method('setRequestEvent');

        $this->getManagerMock()
            ->expects($this->never())
            ->method('receiveRequest');

        $this->assertInstanceOf(
            $this->getKernelListenerClass(),
            $this->buildKernelListener()->onKernelRequest(
                $request
            )
        );
    }

    public function testOnKernelRequestError(): void
    {
        $this->expectException(TypeError::class);
        $this->buildKernelListenerWithStubs()->onKernelRequest(new stdClass());
    }
}
