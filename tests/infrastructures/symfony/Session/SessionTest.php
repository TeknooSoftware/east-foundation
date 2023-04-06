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

namespace Teknoo\Tests\East\FoundationBundle\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\Session\Session;

/**
 * Class SessionTest
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\FoundationBundle\Session\Session
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SymfonySession
     */
    private $symfonySession;

    /**
     * @return SymfonySession|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getSymfonySession(): SymfonySession
    {
        if (!$this->symfonySession instanceof SymfonySession) {
            $this->symfonySession = $this->createMock(SymfonySession::class);
        }

        return $this->symfonySession;
    }

    /**
     * @return Session
     */
    public function buildSession(): Session
    {
        return new Session($this->getSymfonySession());
    }

    public function testSetBadArgument()
    {
        $this->expectException(\TypeError::class);
        $this->buildSession()->set(new \stdClass(), '');
    }
    
    public function testSet()
    {
        $this->getSymfonySession()
            ->expects(self::once())
            ->method('set')
            ->with('foo', 'bar');

        self::assertInstanceOf(
            Session::class,
            $this->buildSession()->set('foo', 'bar')
        );
    }

    public function testGetBadArgument()
    {
        $this->expectException(\TypeError::class);
        $promise = $this->createMock(PromiseInterface::class);
        $this->buildSession()->get(new \stdClass(), $promise);
    }

    public function testGetBadArgumentPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildSession()->get('foo', new \stdClass());
    }
    
    public function testGetFound()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->with('bar')
            ->willReturnSelf();
        $promise->expects(self::never())
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

        self::assertInstanceOf(
            Session::class,
            $this->buildSession()->get('foo', $promise)
        );
    }

    public function testGetNotFound()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())
            ->method('success');
        $promise->expects(self::once())
            ->method('fail');

        $this->getSymfonySession()
            ->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->getSymfonySession()
            ->expects($this->never())
            ->method('get');

        self::assertInstanceOf(
            Session::class,
            $this->buildSession()->get('foo', $promise)
        );
    }

    public function testRemoveBadArgument()
    {
        $this->expectException(\TypeError::class);
        $this->buildSession()->remove(new \stdClass());
    }

    public function testRemove()
    {
        $this->getSymfonySession()
            ->expects(self::once())
            ->method('remove')
            ->with('foo');

        self::assertInstanceOf(
            Session::class,
            $this->buildSession()->remove('foo')
        );
    }

    public function testClear()
    {
        $this->getSymfonySession()
            ->expects(self::once())
            ->method('clear');

        self::assertInstanceOf(
            Session::class,
            $this->buildSession()->clear()
        );
    }
}
