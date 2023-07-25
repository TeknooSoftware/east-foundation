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

namespace Teknoo\Tests\East\Foundation\Http\Bowl\PSR15;

use DateTime;
use DateTimeInterface;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Bowl\PSR15\FiberHandlerBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Tests\Recipe\Bowl\AbstractBowlTests;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Foundation\Http\Bowl\PSR15\FiberHandlerBowl
 */
class FiberHandlerBowlTest extends AbstractBowlTests
{
    protected function getCallable()
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('foo-bar');
            }
        };
    }

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

    protected function getMapping()
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    public function buildBowl(): BowlInterface
    {
        return new FiberHandlerBowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }

    public function testExecute()
    {
        $values = $this->getValidWorkPlan();
        self::assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute(
                $this->createMock(ChefInterface::class),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
}
