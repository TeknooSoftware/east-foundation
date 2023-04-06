<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Foundation\Router\Result
 */
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
