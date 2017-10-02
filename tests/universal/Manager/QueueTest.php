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

namespace Teknoo\Tests\East\Foundation\Manager;

use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\States\Exception\MethodNotImplemented;

/**
 * Class QueueTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Foundation\Manager\Queue\Queue
 * @covers \Teknoo\East\Foundation\Manager\Queue\States\Editing
 * @covers \Teknoo\East\Foundation\Manager\Queue\States\Executing
 */
class QueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Queue
     */
    private function buildQueue(): Queue
    {
        return new Queue();
    }

    /**
     * @return string
     */
    public function getManagerClass(): string
    {
        return Queue::class;
    }

    /**
     * @expectedException \TypeError
     */
    public function testAddBadMiddleware()
    {
        $this->buildQueue()->add(new \stdClass(), 123);
    }

    /**
     * @expectedException \TypeError
     */
    public function testAddBadPriority()
    {
        $this->buildQueue()->add($this->createMock(MiddlewareInterface::class), new \stdClass());
    }

    public function testAdd()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildQueue()->add(
                $this->createMock(MiddlewareInterface::class),
                123
            )
        );
    }

    /**
     * @expectedException \Teknoo\States\Exception\MethodNotImplemented
     */
    public function testAddWhenCompiled()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add(
                $this->createMock(MiddlewareInterface::class),
                123
            )
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );

        $queue->add(
            $this->createMock(MiddlewareInterface::class),
            123
        );
    }

    public function testBuild()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add(
                $this->createMock(MiddlewareInterface::class),
                123
            )
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );
    }

    /**
     * @expectedException \Teknoo\States\Exception\MethodNotImplemented
     */
    public function testBuildWhenCompiled()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add(
                $this->createMock(MiddlewareInterface::class),
                123
            )
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );

        $queue->build();
    }

    public function testIterateEmpty()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );

        $i = 0;
        foreach ($queue->iterate() as $middleware) {
            $i++;
        }

        self::assertEquals(0, $i);
    }

    public function testIterate()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );

        $i = 0;
        foreach ($queue->iterate() as $middleware) {
            $i++;
        }

        self::assertEquals(3, $i);
    }

    /**
     * @expectedException \Teknoo\States\Exception\MethodNotImplemented
     */
    public function testStopWhenNotCompiled()
    {
        $this->buildQueue()->stop();
    }

    public function testIterateAndStop()
    {
        $queue = $this->buildQueue();

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->add($this->createMock(MiddlewareInterface::class))
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $queue->build()
        );

        $i = 0;
        foreach ($queue->iterate() as $middleware) {
            $i++;
            $queue->stop();
        }

        self::assertEquals(1, $i);
    }
}