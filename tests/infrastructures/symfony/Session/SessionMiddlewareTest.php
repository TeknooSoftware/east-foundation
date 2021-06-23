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

namespace Teknoo\Tests\East\FoundationBundle\Session;

use Psr\Http\Message\MessageInterface;
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Session\SessionMiddleware
 */
class SessionMiddlewareTest extends \PHPUnit\Framework\TestCase
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
            ->with(SessionInterface::ATTRIBUTE_KEY, $this->callback(function ($object) {
                return $object instanceof Session;
            }))
            ->willReturn($requestUpdated);

        $manager->expects(self::any())
            ->method('continueExecution')
            ->with($client, $requestUpdated)
            ->willReturnSelf();

        $manager->expects(self::once())
            ->method('updateMessage')
            ->with($requestUpdated)
            ->willReturnSelf();

        self::assertInstanceOf(
            SessionMiddleware::class,
            $this->buildMiddleware()->execute($client, $request, $manager)
        );
    }
}
