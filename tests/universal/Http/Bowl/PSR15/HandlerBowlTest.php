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

namespace Teknoo\Tests\East\Foundation\Http\Bowl\PSR15;

use DateTime;
use DateTimeInterface;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Bowl\PSR15\HandlerBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Recipe\Value;
use Teknoo\Tests\Recipe\Bowl\AbstractBowlTests;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(HandlerBowl::class)]
class HandlerBowlTest extends AbstractBowlTests
{
    protected function getCallable(): \Psr\Http\Server\RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('foo-bar');
            }
        };
    }

    #[\Override]
    protected function getValidWorkPlan(): array
    {
        return [
            'foo' => 'foo',
            'foo2' => 'bar2',
            'now' => (new DateTime('2018-01-01')),
            DateTimeInterface::class => (new DateTime('2018-01-02')),
            ServerRequestInterface::class => $this->createMock(ServerRequestInterface::class),
            ClientInterface::class => $this->createMock(ClientInterface::class),
        ];
    }

    protected function getMapping(): array
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    public function buildBowl(): BowlInterface
    {
        return new HandlerBowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }

    public function buildBowlWithMappingValue(): BowlInterface
    {
        return new HandlerBowl(
            $this->getCallable(),
            [
                'bar' => new Value('ValueFoo1'),
                'bar2' => new Value('ValueFoo2'),
            ],
            'bowlClass'
        );
    }

    #[\Override]
    public function testExecute(): void
    {
        $values = $this->getValidWorkPlan();
        $this->assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute(
                $this->createMock(ChefInterface::class),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    #[\Override]
    public function testExecuteWithValue(): void
    {
        $this->assertTrue(true);
    }
}
