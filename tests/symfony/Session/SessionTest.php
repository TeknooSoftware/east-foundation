<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\Session\Session;

/**
 * Class SessionTest
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Session\Session
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SymfonySession
     */
    private $symfonySession;

    /**
     * @return SymfonySession|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @expectedException \TypeError
     */
    public function testSetBadArgument()
    {
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

    /**
     * @expectedException \TypeError
     */
    public function testGetBadArgument()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $this->buildSession()->get(new \stdClass(), $promise);
    }

    /**
     * @expectedException \TypeError
     */
    public function testGetBadArgumentPromise()
    {
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

    /**
     * @expectedException \TypeError
     */
    public function testRemoveBadArgument()
    {
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
