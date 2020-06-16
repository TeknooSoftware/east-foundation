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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Foundation\Router\Result
 */
class ResultTest extends AbstractResultTest
{
    public function buildResult(): ResultInterface
    {
        return new Result(function (int $a, string $b, \DateTime $d, $test = 'foo') {
        }, null);
    }

    public function buildResultWithNext(): ResultInterface
    {
        return new Result(
            function () {
            },
            new Result(function (int $a, string $b, \DateTime $d, $test = 'foo') {
            })
        );
    }

    public function testValueObjectBehaviorConstructor()
    {
        $this->expectException(ImmutableException::class);
        $this->buildResult()->__construct(function (int $a, string $b, \DateTime $d, $test = 'foo') {
        }, null);
    }

    public function testConstructBadNext()
    {
        $this->expectException(\TypeError::class);
        new Result(function () {
        }, new \DateTime());
    }

    public function testGetParmetersValueFromClosure()
    {
        $parameters = $this->buildResult()->getParameters();

        self::assertIsArray($parameters);
        self::assertCount(4, $parameters);

        self::assertInstanceOf(ParameterInterface::class, $parameters['a']);
        self::assertEquals('a', $parameters['a']->getName());
        self::assertFalse($parameters['a']->hasDefaultValue());
        self::assertNull($parameters['a']->getDefaultValue());
        self::assertFalse($parameters['a']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['b']);
        self::assertEquals('b', $parameters['b']->getName());
        self::assertFalse($parameters['b']->hasDefaultValue());
        self::assertNull($parameters['b']->getDefaultValue());
        self::assertFalse($parameters['b']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['d']);
        self::assertEquals('d', $parameters['d']->getName());
        self::assertFalse($parameters['d']->hasDefaultValue());
        self::assertNull($parameters['d']->getDefaultValue());
        self::assertTrue($parameters['d']->hasClass());
        self::assertInstanceOf(\ReflectionClass::class, $parameters['d']->getClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['test']);
        self::assertEquals('test', $parameters['test']->getName());
        self::assertTrue($parameters['test']->hasDefaultValue());
        self::assertEquals('foo', $parameters['test']->getDefaultValue());
        self::assertFalse($parameters['test']->hasClass());
    }

    public function testGetParmetersValueFromInvokable()
    {
        $invokable = new class() {
            public function __invoke(int $a, string $b, \DateTime $d, $test = 'foo')
            {
            }
        };

        $result = new Result($invokable, null);
        $parameters = $result->getParameters();

        self::assertIsArray($parameters);
        self::assertCount(4, $parameters);

        self::assertInstanceOf(ParameterInterface::class, $parameters['a']);
        self::assertEquals('a', $parameters['a']->getName());
        self::assertFalse($parameters['a']->hasDefaultValue());
        self::assertNull($parameters['a']->getDefaultValue());
        self::assertFalse($parameters['a']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['b']);
        self::assertEquals('b', $parameters['b']->getName());
        self::assertFalse($parameters['b']->hasDefaultValue());
        self::assertNull($parameters['b']->getDefaultValue());
        self::assertFalse($parameters['b']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['d']);
        self::assertEquals('d', $parameters['d']->getName());
        self::assertFalse($parameters['d']->hasDefaultValue());
        self::assertNull($parameters['d']->getDefaultValue());
        self::assertTrue($parameters['d']->hasClass());
        self::assertInstanceOf(\ReflectionClass::class, $parameters['d']->getClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['test']);
        self::assertEquals('test', $parameters['test']->getName());
        self::assertTrue($parameters['test']->hasDefaultValue());
        self::assertEquals('foo', $parameters['test']->getDefaultValue());
        self::assertFalse($parameters['test']->hasClass());
    }

    public function testGetParmetersValueFromMethod()
    {
        $object = new class() {
            public function test(int $a, string $b, \DateTime $d, $test = 'foo')
            {
            }
        };

        $result = new Result([$object, 'test'], null);
        $parameters = $result->getParameters();

        self::assertIsArray($parameters);
        self::assertCount(4, $parameters);

        self::assertInstanceOf(ParameterInterface::class, $parameters['a']);
        self::assertEquals('a', $parameters['a']->getName());
        self::assertFalse($parameters['a']->hasDefaultValue());
        self::assertNull($parameters['a']->getDefaultValue());
        self::assertFalse($parameters['a']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['b']);
        self::assertEquals('b', $parameters['b']->getName());
        self::assertFalse($parameters['b']->hasDefaultValue());
        self::assertNull($parameters['b']->getDefaultValue());
        self::assertFalse($parameters['b']->hasClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['d']);
        self::assertEquals('d', $parameters['d']->getName());
        self::assertFalse($parameters['d']->hasDefaultValue());
        self::assertNull($parameters['d']->getDefaultValue());
        self::assertTrue($parameters['d']->hasClass());
        self::assertInstanceOf(\ReflectionClass::class, $parameters['d']->getClass());

        self::assertInstanceOf(ParameterInterface::class, $parameters['test']);
        self::assertEquals('test', $parameters['test']->getName());
        self::assertTrue($parameters['test']->hasDefaultValue());
        self::assertEquals('foo', $parameters['test']->getDefaultValue());
        self::assertFalse($parameters['test']->hasClass());
    }
}
