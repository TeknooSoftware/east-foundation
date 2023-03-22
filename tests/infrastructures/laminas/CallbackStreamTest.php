<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Diactoros;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Diactoros\CallbackStream;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Diactoros\CallbackStream
 */
class CallbackStreamTest extends TestCase
{
    public function testBind()
    {
        self::assertInstanceOf(
            CallbackStream::class,
            (new CallbackStream(function () {}))->bind(function () {})
        );
    }

    public function testUnbind()
    {
        self::assertInstanceOf(
            CallbackStream::class,
            (new CallbackStream(function () {}))->unbind()
        );
    }
}
