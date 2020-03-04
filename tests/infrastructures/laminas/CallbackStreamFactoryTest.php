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

namespace Teknoo\Tests\East\Diactoros;

use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Diactoros\CallbackStreamFactory;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Diactoros\CallbackStreamFactory
 */
class CallbackStreamFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function buildFactory(): CallbackStreamFactory
    {
        return new CallbackStreamFactory();
    }

    public function testCreateStream()
    {
        self::assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStream('foo')
        );

        self::assertEquals(
            'foo',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromFile()
    {
        self::assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromFile('php://memory', 'r')
        );

        self::assertEquals(
            '',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromFileNotReadable()
    {
        self::assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromFile('/aaaaa', 'r')
        );

        $this->expectException(\RuntimeException::class);
        $stream->getContents();
    }

    public function testcreateStreamFromResource()
    {
        $hf = fopen('php://memory', 'rw+');
        fwrite($hf, 'fooBarContent');
        \fseek($hf, 0);

        self::assertInstanceOf(
            CallbackStream::class,
            $stream = $this->buildFactory()->createStreamFromResource($hf)
        );

        self::assertEquals(
            'fooBarContent',
            $stream->getContents($hf)
        );
    }
}