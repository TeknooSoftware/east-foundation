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

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractParameterTests extends \PHPUnit\Framework\TestCase
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

    public function testValueObjectBehaviorSetException()
    {
        $this->expectException(ImmutableException::class);
        $this->buildParameter()->foo = 'bar';
    }

    public function testValueObjectBehaviorUnsetException()
    {
        $this->expectException(ImmutableException::class);
        unset($this->buildParameter()->foo);
    }

    public function testValueObjectBehaviorConstructor()
    {
        $this->expectException(ImmutableException::class);
        $this->buildParameter()->__construct();
    }

    public function testGetName()
    {
        self::assertIsString($this->buildParameter()->getName());
        self::assertEquals('foo', $this->buildParameter()->getName());
    }

    public function testHasDefaultValue()
    {
        self::assertIsBool($this->buildParameter()->hasDefaultValue());
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
        self::assertIsBool($this->buildParameter()->hasClass());
        self::assertFalse($this->buildParameter()->hasClass());
        self::assertTrue($this->buildParameterWithClass()->hasClass());
    }


    public function testGetClass()
    {
        self::assertInstanceOf(\ReflectionClass::class, $this->buildParameterWithClass()->getClass());
    }

    public function testGetClassNotDefined()
    {
        $this->expectException(\RuntimeException::class);
        self::assertInstanceOf(\ReflectionClass::class, $this->buildParameter()->getClass());
    }
}
