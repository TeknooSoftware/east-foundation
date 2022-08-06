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

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return ResultInterface
     */
    abstract public function buildResult(): ResultInterface;
    /**
     * @return ResultInterface
     */
    abstract public function buildResultWithNext(): ResultInterface;

    public function testValueObjectBehaviorSetException()
    {
        $this->expectException(ImmutableException::class);
        $this->buildResult()->foo = 'bar';
    }

    public function testValueObjectBehaviorUnsetException()
    {
        $this->expectException(ImmutableException::class);
        unset($this->buildResult()->foo);
    }

    public function testValueObjectBehaviorConstructor()
    {
        $this->expectException(ImmutableException::class);
        $this->buildResult()->__construct();
    }

    public function testGetController()
    {
        self::assertIsCallable($this->buildResult()->getController());
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
