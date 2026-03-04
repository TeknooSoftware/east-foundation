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

namespace Teknoo\Tests\East\FoundationBundle\UX\Components;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Teknoo\East\FoundationBundle\UX\Components\LiveTrait;

/**
 * Class LiveTraitTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversTrait(LiveTrait::class)]
class LiveTraitTest extends TestCase
{
    /**
     * Create a concrete class using the trait for testing purposes
     */
    private function createTraitInstance(RequestStack $requestStack): object
    {
        return new class ($requestStack) {
            use LiveTrait;
        };
    }

    public function testConstructorInitializesOriginalPath(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/user/profile/123');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $instance = $this->createTraitInstance($requestStack);

        $this->assertEquals('/user/profile/123', $instance->originalPath);
    }

    public function testConstructorInitializesOriginalPathWithRootPath(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $instance = $this->createTraitInstance($requestStack);

        $this->assertEquals('/', $instance->originalPath);
    }

    public function testConstructorWithEmptyPath(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $instance = $this->createTraitInstance($requestStack);

        $this->assertEquals('', $instance->originalPath);
    }
}
