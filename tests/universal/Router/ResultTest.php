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

use Error;
use TypeError;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Result::class)]
class ResultTest extends AbstractResultTests
{
    public function buildResult(): ResultInterface
    {
        return new Result(function (int $a, string $b, \DateTime $d, $test = 'foo'): void {
        }, null);
    }

    public function buildResultWithNext(): ResultInterface
    {
        return new Result(
            function (): void {
            },
            new Result(function (int $a, string $b, \DateTime $d, $test = 'foo'): void {
            })
        );
    }

    #[\Override]
    public function testValueObjectBehaviorConstructor(): void
    {
        $this->expectException(Error::class);
        $this->buildResult()->__construct(function (int $a, string $b, \DateTime $d, $test = 'foo'): void {
        }, null);
    }

    public function testConstructBadNext(): void
    {
        $this->expectException(TypeError::class);
        new Result(function (): void {
        }, new \DateTime());
    }
}
