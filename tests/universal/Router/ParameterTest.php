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

namespace Teknoo\Tests\East\Foundation\Router;

use Teknoo\East\Foundation\Router\Parameter;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Foundation\Router\Parameter
 */
class ParameterTest extends AbstractParameterTests
{
    public function buildParameter(): ParameterInterface
    {
        return new Parameter('foo', false, null, null);
    }

    public function buildParameterWithDefaultValue(): ParameterInterface
    {
        return new Parameter('foo', true, 'bar', null);
    }

    public function buildParameterWithClass(): ParameterInterface
    {
        return new Parameter('foo', false, null, new \ReflectionClass(\DateTime::class));
    }

    public function testValueObjectBehaviorConstructor()
    {
        $this->expectException(\Error::class);
        $this->buildParameter()->__construct('foo', false, null, null);
    }

    public function testConstructBadClass()
    {
        $this->expectException(\TypeError::class);
        new Parameter('foo', false, null, new \DateTime());
    }
}
