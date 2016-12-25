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

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ParameterInterface;

abstract class AbstractParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ParameterInterface
     */
    abstract public function buildParameter(): ParameterInterface;

    /**
     * @return ParameterInterface
     */
    abstract public function buildParameterWithDefaultValue(): ParameterInterface;

    /**
     * @return ParameterInterface
     */
    abstract public function buildParameterWithClass(): ParameterInterface;

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorSetException()
    {
        $this->buildParameter()->foo = 'bar';
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorUnsetException()
    {
        unset($this->buildParameter()->foo);
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorConstructor()
    {
        $this->buildParameter()->__construct();
    }

    public function testGetName()
    {
        self::assertInternalType('string', $this->buildParameter()->getName());
        self::assertEquals('foo', $this->buildParameter()->getName());
    }

    public function testHasDefaultValue()
    {
        self::assertInternalType('bool', $this->buildParameter()->hasDefaultValue());
        self::assertFalse($this->buildParameter()->hasDefaultValue());
        self::assertTrue($this->buildParameterWithDefaultValue()->hasDefaultValue());
    }

    public function testGetDefaultValue()
    {
        self::assertEmpty($this->buildParameter()->getDefaultValue());
        self::assertEquals('bar', $this->buildParameterWithDefaultValue()->getDefaultValue());
    }

    public function testHasClass()
    {
        self::assertInternalType('bool', $this->buildParameter()->hasClass());
        self::assertFalse($this->buildParameter()->hasClass());
        self::assertTrue($this->buildParameterWithClass()->hasClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testGetClassWIthNoClass()
    {
        $this->buildParameter()->getClass();
    }

    public function testGetClass()
    {
        self::assertInstanceOf(\ReflectionClass::class, $this->buildParameterWithClass()->getClass());
    }
}
