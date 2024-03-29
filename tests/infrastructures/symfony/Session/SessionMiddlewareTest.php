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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Session;

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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\FoundationBundle\Session\SessionMiddleware
 */
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

        $manager->expects(self::never())
            ->method('continueExecution');

        $request->expects(self::never())
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

        $manager->expects(self::never())
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
        $sfRequest->expects(self::any())
            ->method('getSession')
            ->willReturn($session);

        $request->expects(self::any())
            ->method('getAttribute')
            ->with('request')
            ->willReturn($sfRequest);


        $request->expects(self::once())
            ->method('withAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY, $this->callback(fn($object) => $object instanceof Session))
            ->willReturn($requestUpdated);

        $manager->expects(self::any())
            ->method('continueExecution')
            ->with($client, $requestUpdated)
            ->willReturnSelf();

        $manager->expects(self::once())
            ->method('updateMessage')
            ->with($requestUpdated)
            ->willReturnSelf();

        $manager->expects(self::once())
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
        $sfRequest->expects(self::never())
            ->method('getSession');

        $sfRequest->attributes = new ParameterBag();
        $sfRequest->attributes->set('_stateless', true);

        $request->expects(self::any())
            ->method('getAttribute')
            ->with('request')
            ->willReturn($sfRequest);

        $request->expects(self::never())
            ->method('withAttribute');

        $manager->expects(self::never())
            ->method('continueExecution');

        $manager->expects(self::never())
            ->method('updateMessage');

        $manager->expects(self::never())
            ->method('updateWorkPlan');

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $request, $manager)
        );
    }
}
