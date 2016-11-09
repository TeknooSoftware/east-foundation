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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
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

/**
 * @covers \Teknoo\East\Foundation\Router\Result
 */
class ResultTest extends AbstractResultTest
{
    public function buildResult(): ResultInterface
    {
        return new Result(function(int $a, string $b, \DateTime $d, $test='foo'){}, null);
    }

    public function buildResultWithNext(): ResultInterface
    {
        return new Result(
            function(){},
            new Result(function(int $a, string $b, \DateTime $d, $test='foo'){})
        );
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorConstructor()
    {
        $this->buildResult()->__construct(function(int $a, string $b, \DateTime $d, $test='foo'){}, null);
    }
}