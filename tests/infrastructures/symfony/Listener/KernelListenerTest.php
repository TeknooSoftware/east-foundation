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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Listener;

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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(KernelListener::class)]
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
        $request->expects($this->any())->method('getRequest')->willReturn(new Request());

        $psrRquest = $this->createMock(ServerRequestInterface::class);
        $psrRquest->expects($this->any())->method('withAttribute')->willReturnSelf();

        $this->getFactoryMock()
            ->expects($this->any())
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
        $request->expects($this->any())->method('getRequest')->willReturn($symfonyRequest);

        $this->getFactoryMock()
            ->expects($this->any())
            ->method('createRequest')
            ->willReturn($this->createMock(ServerRequestInterface::class));

        $this->getClientWithResponseEventInterfaceMock()
            ->expects($this->never())
            ->method('setRequestEvent');

        $this->getManagerMock()
            ->expects($this->never())
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
