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

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Result::class)]
class ResultTest extends AbstractResultTests
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
        $this->expectException(\Error::class);
        $this->buildResult()->__construct(function (int $a, string $b, \DateTime $d, $test = 'foo') {
        }, null);
    }

    public function testConstructBadNext()
    {
        $this->expectException(\TypeError::class);
        new Result(function () {
        }, new \DateTime());
    }
}
