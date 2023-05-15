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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Twig\Template;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Twig\Template\Engine;
use Twig\Environment;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Twig\Template\Engine
 */
class EngineTest extends TestCase
{
    private ?Environment $twig = null;

    /**
     * @return Environment|MockObject
     */
    public function getTwig(): Environment
    {
        if (!$this->twig instanceof Environment) {
            $this->twig = $this->createMock(Environment::class);
        }

        return $this->twig;
    }

    public function buildEngine(): Engine
    {
        return new Engine($this->getTwig());
    }

    public function testRenderNotCallResult()
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->with(self::callback(fn($r) => $r instanceof ResultInterface));

        self::assertInstanceOf(
            EngineInterface::class,
            $this->buildEngine()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }

    public function testRenderCallingResult()
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $this->getTwig()
            ->expects(self::once())
            ->method('render')
            ->with($view, $parameters)
            ->willReturn('bar');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->willReturnCallback(
                function (ResultInterface $result) use ($promise) {
                    self::assertEquals('bar', (string) $result);

                    return $promise;
                }
            );

        self::assertInstanceOf(
            EngineInterface::class,
            $this->buildEngine()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }

    public function testRenderError()
    {
        $view = 'foo';
        $parameters = ['foo' => 'bar'];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->willThrowException(new \RuntimeException('foo'));
        $promise->expects(self::once())->method('fail');

        self::assertInstanceOf(
            EngineInterface::class,
            $this->buildEngine()->render(
                $promise,
                $view,
                $parameters
            )
        );
    }
}
