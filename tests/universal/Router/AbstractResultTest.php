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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ResultInterface;

abstract class AbstractResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ResultInterface
     */
    abstract public function buildResult(): ResultInterface;
    /**
     * @return ResultInterface
     */
    abstract public function buildResultWithNext(): ResultInterface;

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorSetException()
    {
        $this->buildResult()->foo = 'bar';
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorUnsetException()
    {
        unset($this->buildResult()->foo);
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorConstructor()
    {
        $this->buildResult()->__construct();
    }

    public function testGetController()
    {
        self::assertInternalType('callable', $this->buildResult()->getController());
    }

    public function testGetParameters()
    {
        self::assertInternalType('array', $this->buildResult()->getParameters());
    }

    public function testGetNextWithNoNext()
    {
        self::assertNull($this->buildResult()->getNext());
    }

    public function testGetNext()
    {
        self::assertInstanceOf(ResultInterface::class, $this->buildResultWithNext()->getNext());
    }
}