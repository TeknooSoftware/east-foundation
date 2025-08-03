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
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractResultTests extends TestCase
{
    abstract public function buildResult(): ResultInterface;

    abstract public function buildResultWithNext(): ResultInterface;

    public function testValueObjectBehaviorSetException(): void
    {
        $this->expectException(ImmutableException::class);
        $this->buildResult()->foo = 'bar';
    }

    public function testValueObjectBehaviorUnsetException(): void
    {
        $this->expectException(ImmutableException::class);
        unset($this->buildResult()->foo);
    }

    public function testValueObjectBehaviorConstructor(): void
    {
        $this->expectException(ImmutableException::class);
        $this->buildResult()->__construct();
    }

    public function testGetController(): void
    {
        $this->assertIsCallable($this->buildResult()->getController());
    }

    public function testGetNextWithNoNext(): void
    {
        $this->assertNull($this->buildResult()->getNext());
    }

    public function testGetNext(): void
    {
        $this->assertInstanceOf(ResultInterface::class, $this->buildResultWithNext()->getNext());
    }
}
