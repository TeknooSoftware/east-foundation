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

namespace Teknoo\Tests\East\FoundationBundle\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\Session\Session;
use TypeError;

/**
 * Class SessionTest
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
    private ?SymfonySession $symfonySession = null;

    public function getSymfonySession(): SymfonySession&MockObject
    {
        if (
            !$this->symfonySession instanceof SymfonySession
            || !$this->symfonySession instanceof MockObject
        ) {
            $this->symfonySession = $this->createMock(SymfonySession::class);
        }

        return $this->symfonySession;
    }

    public function getSymfonySessionStub(): SymfonySession&Stub
    {
        if (!$this->symfonySession instanceof SymfonySession) {
            $this->symfonySession = $this->createStub(SymfonySession::class);
        }

        return $this->symfonySession;
    }

    public function buildSession(): Session
    {
        return new Session($this->getSymfonySession());
    }

    public function buildSessionWithStub(): Session
    {
        return new Session($this->getSymfonySessionStub());
    }

    public function testSetBadArgument(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSessionWithStub()->set(new stdClass(), '');
    }

    public function testSet(): void
    {
        $this->getSymfonySession()
            ->expects($this->once())
            ->method('set')
            ->with('foo', 'bar');

        $this->assertInstanceOf(
            Session::class,
            $this->buildSession()->set('foo', 'bar')
        );
    }

    public function testGetBadArgument(): void
    {
        $this->expectException(TypeError::class);
        $promise = $this->createStub(PromiseInterface::class);
        $this->buildSessionWithStub()->get(new stdClass(), $promise);
    }

    public function testGetBadArgumentPromise(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSessionWithStub()->get('foo', new stdClass());
    }

    public function testGetFound(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with('bar')
            ->willReturnSelf();
        $promise->expects($this->never())
            ->method('fail');

        $this->getSymfonySession()
            ->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $this->getSymfonySession()
            ->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn('bar');

        $this->assertInstanceOf(
            Session::class,
            $this->buildSession()->get('foo', $promise)
        );
    }

    public function testGetNotFound(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('success');
        $promise->expects($this->once())
            ->method('fail');

        $this->getSymfonySession()
            ->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->getSymfonySession()
            ->expects($this->never())
            ->method('get');

        $this->assertInstanceOf(
            Session::class,
            $this->buildSession()->get('foo', $promise)
        );
    }

    public function testRemoveBadArgument(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSessionWithStub()->remove(new stdClass());
    }

    public function testRemove(): void
    {
        $this->getSymfonySession()
            ->expects($this->once())
            ->method('remove')
            ->with('foo');

        $this->assertInstanceOf(
            Session::class,
            $this->buildSession()->remove('foo')
        );
    }

    public function testClear(): void
    {
        $this->getSymfonySession()
            ->expects($this->once())
            ->method('clear');

        $this->assertInstanceOf(
            Session::class,
            $this->buildSession()->clear()
        );
    }
}
