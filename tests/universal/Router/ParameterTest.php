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
use Teknoo\East\Foundation\Router\Parameter;
use Teknoo\East\Foundation\Router\ParameterInterface;
use Teknoo\Immutable\Exception\ImmutableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Parameter::class)]
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
