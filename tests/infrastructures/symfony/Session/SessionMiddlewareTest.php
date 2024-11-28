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

namespace Teknoo\Tests\East\FoundationBundle\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\FoundationBundle\Session\Session;
use Teknoo\East\FoundationBundle\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * Class SessionMiddlewareTest
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SessionMiddleware::class)]
class SessionMiddlewareTest extends TestCase
{
    public function buildMiddleware()
    {
        return new SessionMiddleware();
    }

    public function testHasNoSymfonyRequest()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->never())
            ->method('continueExecution');

        $request->expects($this->never())
            ->method('withAttribute');

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $request, $manager)
        );
    }

    public function testHasMessage()
    {
        $message = $this->createMock(MessageInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->never())
            ->method('continueExecution');

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $message, $manager)
        );
    }

    public function testHasSymfonyRequest()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $requestUpdated = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $sfRequest = $this->createMock(Request::class);
        $sfRequest->attributes = new ParameterBag();

        $session = $this->createMock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class);
        $sfRequest->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        $request->expects($this->any())
            ->method('getAttribute')
            ->with('request')
            ->willReturn($sfRequest);


        $request->expects($this->once())
            ->method('withAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY, $this->callback(fn($object) => $object instanceof Session))
            ->willReturn($requestUpdated);

        $manager->expects($this->any())
            ->method('continueExecution')
            ->with($client, $requestUpdated)
            ->willReturnSelf();

        $manager->expects($this->once())
            ->method('updateMessage')
            ->with($requestUpdated)
            ->willReturnSelf();

        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $request, $manager)
        );
    }

    public function testHasSymfonyRequestInStateless()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $requestUpdated = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $sfRequest = $this->createMock(Request::class);
        $sfRequest->expects($this->never())
            ->method('getSession');

        $sfRequest->attributes = new ParameterBag();
        $sfRequest->attributes->set('_stateless', true);

        $request->expects($this->any())
            ->method('getAttribute')
            ->with('request')
            ->willReturn($sfRequest);

        $request->expects($this->never())
            ->method('withAttribute');

        $manager->expects($this->never())
            ->method('continueExecution');

        $manager->expects($this->never())
            ->method('updateMessage');

        $manager->expects($this->never())
            ->method('updateWorkPlan');

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $request, $manager)
        );
    }
}
