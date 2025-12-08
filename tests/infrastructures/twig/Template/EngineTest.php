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

namespace Teknoo\Tests\East\Twig\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Twig\Template\Engine;
use Twig\Environment;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Engine::class)]
class EngineTest extends TestCase
{
    private ?Environment $twig = null;

    /**
     * @return Environment|MockObject
     */
    public function getTwig(): Environment&MockObject
    {
        if (
            !$this->twig instanceof Environment
            || !$this->twig instanceof MockObject
        ) {
            $this->twig = $this->createMock(Environment::class);
        }

        return $this->twig;
    }

    public function getTwigStub(): Environment&Stub
    {
        if (!$this->twig instanceof Environment) {
            $this->twig = $this->createStub(Environment::class);
        }

        return $this->twig;
    }

    public function buildEngine(): Engine
    {
        return new Engine($this->getTwig());
    }

    public function buildEngineWithStub(): Engine
    {
        return new Engine($this->getTwigStub());
    }

    public function testRenderNotCallResult(): void
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with(self::callback(fn ($r): bool => $r instanceof ResultInterface));

        $this->assertInstanceOf(
            EngineInterface::class,
            $this->buildEngineWithStub()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }

    public function testRenderCallingResult(): void
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $this->getTwig()
            ->expects($this->once())
            ->method('render')
            ->with($view, $parameters)
            ->willReturn('bar');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willReturnCallback(
                function (ResultInterface $result) use ($promise): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertSame('bar', (string) $result);

                    return $promise;
                }
            );

        $this->assertInstanceOf(
            EngineInterface::class,
            $this->buildEngine()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }

    public function testRenderError(): void
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willThrowException(new \RuntimeException('foo'));
        $promise->expects($this->once())->method('fail');

        $this->assertInstanceOf(
            EngineInterface::class,
            $this->buildEngineWithStub()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }
}
