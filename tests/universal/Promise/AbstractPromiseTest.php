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

namespace Teknoo\Tests\East\Foundation\Promise;

use Teknoo\East\Foundation\Promise\PromiseInterface;

abstract class AbstractPromiseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param callable|null $onSuccess
     * @param callable|null $onFail
     * @return PromiseInterface
     */
    abstract public function buildPromise($onSuccess, $onFail): PromiseInterface;

    /**
     * @expectedException \Throwable
     */
    public function testConstructorBadSuccessCallable()
    {
        $this->buildPromise('fooBar', function() {});
    }

    /**
     * @expectedException \Throwable
     */
    public function testConstructorBadFailCallable()
    {
        $this->buildPromise(function() {}, 'fooBar');
    }

    public function testConstructor()
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(function() {}, function() {})
        );
    }

    public function testConstructorAtNull()
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(null, null)
        );
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testConstructorImmutable()
    {
        $this->buildPromise(function() {}, function() {})->__construct(function() {}, function() {});
    }

    /**
     * @expectedException \Throwable
     */
    public function testNextSetNotCallable()
    {
        $this->buildPromise(function(){}, function(){})->next('fooBar');
    }

    public function testNextSetNull()
    {
        $promise = $this->buildPromise(function() {}, function() {});
        $nextPromise = $promise->next(null);

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallable()
    {
        $promise = $this->buildPromise(function() {}, function() {});
        $nextPromise = $promise->next($this->createMock(PromiseInterface::class));

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testSuccess()
    {
        $called=false;
        $promiseWithSuccessCallback = $this->buildPromise(
            function($result, $next) use (&$called) {
                $called=true;
                self::assertEquals('foo', $result);
                self::assertNull($next);
            },
            function() {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithSuccessCallback->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            null,
            function() {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->success('foo')
        );
    }

    public function testFail()
    {
        $called=false;
        $promiseWithSuccessCallback = $this->buildPromise(
            function() {
                self::fail('Error, success callback must not be called');
            },
            function($result, $next) use (&$called) {
                $called=true;
                self::assertEquals(new \Exception('fooBar'), $result);
                self::assertNull($next);
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithSuccessCallback->fail(new \Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            function() {
                self::fail('Error, success callback must not be called');
            },
            null
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->fail(new \Exception('fooBar'))
        );
    }

    public function testSuccessWithNext()
    {
        $refNext = $this->createMock(PromiseInterface::class);

        $called=false;
        $promiseWithSuccessCallback = $this->buildPromise(
            function($result, $next) use (&$called, $refNext) {
                $called=true;
                self::assertEquals('foo', $result);
                self::assertEquals($refNext, $next);
            },
            function() {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithSuccessCallback->next($refNext)->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            null,
            function() {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->next($refNext)->success('foo')
        );
    }

    public function testFailWithNext()
    {
        $refNext = $this->createMock(PromiseInterface::class);

        $called=false;
        $promiseWithSuccessCallback = $this->buildPromise(
            function() {
                self::fail('Error, success callback must not be called');
            },
            function($result, $next) use (&$called, $refNext) {
                $called=true;
                self::assertEquals(new \Exception('fooBar'), $result);
                self::assertEquals($refNext, $next);
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithSuccessCallback->next($refNext)->fail(new \Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            function() {
                self::fail('Error, success callback must not be called');
            },
            null
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->next($refNext)->fail(new \Exception('fooBar'))
        );
    }
}