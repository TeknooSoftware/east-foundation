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

namespace Teknoo\Tests\East\Foundation\Router;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractParameterTests extends TestCase
{
    abstract public function buildParameter(): ParameterInterface;

    abstract public function buildParameterWithDefaultValue(): ParameterInterface;

    abstract public function buildParameterWithClass(): ParameterInterface;

    public function testValueObjectBehaviorSetException(): void
    {
        $this->expectException(ImmutableException::class);
        $this->buildParameter()->foo = 'bar';
    }

    public function testValueObjectBehaviorUnsetException(): void
    {
        $this->expectException(ImmutableException::class);
        unset($this->buildParameter()->foo);
    }

    public function testValueObjectBehaviorConstructor(): void
    {
        $this->expectException(ImmutableException::class);
        $this->buildParameter()->__construct();
    }

    public function testGetName(): void
    {
        $this->assertIsString($this->buildParameter()->getName());
        $this->assertSame('foo', $this->buildParameter()->getName());
    }

    public function testHasDefaultValue(): void
    {
        $this->assertIsBool($this->buildParameter()->hasDefaultValue());
        $this->assertFalse($this->buildParameter()->hasDefaultValue());
        $this->assertTrue($this->buildParameterWithDefaultValue()->hasDefaultValue());
    }

    public function testGetDefaultValue(): void
    {
        $this->assertEmpty($this->buildParameter()->getDefaultValue());
        $this->assertEquals('bar', $this->buildParameterWithDefaultValue()->getDefaultValue());
    }

    public function testHasClass(): void
    {
        $this->assertIsBool($this->buildParameter()->hasClass());
        $this->assertFalse($this->buildParameter()->hasClass());
        $this->assertTrue($this->buildParameterWithClass()->hasClass());
    }


    public function testGetClass(): void
    {
        $this->assertInstanceOf(\ReflectionClass::class, $this->buildParameterWithClass()->getClass());
    }

    public function testGetClassNotDefined(): void
    {
        $this->expectException(RuntimeException::class);
        $this->assertInstanceOf(\ReflectionClass::class, $this->buildParameter()->getClass());
    }
}
