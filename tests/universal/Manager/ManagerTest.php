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

namespace Teknoo\Tests\East\Foundation\Manager;

use TypeError;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisor;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Tests\Recipe\AbstractChefTests;

/**
 * Class ManagerTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Manager::class)]
class ManagerTest extends AbstractChefTests
{
    public function buildChef(?CookingSupervisorInterface $cookingSupervisor = null): ChefInterface
    {
        return new Manager(cookingSupervisor: $cookingSupervisor ?? new CookingSupervisor());
    }

    public function testReadInConstructor(): void
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $this->assertInstanceOf(
            ManagerInterface::class,
            new Manager($recipe)
        );
    }

    public function getManagerClass(): string
    {
        return Manager::class;
    }

    public function testReceiveRequest(): void
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildChef()->read($this->createMock(RecipeInterface::class))
                ->followSteps([$this->createMock(BowlInterface::class)])
                ->receiveRequest(
                    $this->createMock(ClientInterface::class),
                    $this->createMock(ServerRequestInterface::class)
                )
        );
    }

    public function testReceiveMessage(): void
    {
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $this->buildChef()->read($this->createMock(RecipeInterface::class))
                ->followSteps([$this->createMock(BowlInterface::class)])
                ->receiveRequest(
                    $this->createMock(ClientInterface::class),
                    $this->createMock(MessageInterface::class)
                )
        );
    }

    public function testReceiveRequestErrorClient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->receiveRequest(
                new stdClass(),
                $this->createMock(ServerRequestInterface::class)
            );
    }

    public function testReceiveRequestErrorRequest(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->receiveRequest(
                $this->createMock(ClientInterface::class),
                new stdClass()
            );
    }

    public function testContinueExecutionErrorClient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->continueExecution(
                new stdClass(),
                $this->createMock(ServerRequestInterface::class)
            );
    }

    public function testContinueExecutionErrorRequest(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->continueExecution(
                $this->createMock(ClientInterface::class),
                new stdClass()
            );
    }

    public function testUpdateMessageExecutionErrorRequest(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->updateWorkPlan(
                new stdClass()
            );
    }

    public function testStopWithRequest(): void
    {
        $middleware = new readonly class ($this) implements MiddlewareInterface {
            private \Teknoo\Tests\East\Foundation\Manager\ManagerTest $testSuite;

            public function __construct(ManagerTest $that)
            {
                $this->testSuite = $that;
            }

            public function execute(
                ClientInterface $client,
                MessageInterface $message,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $this->testSuite->assertInstanceOf(
                    $this->testSuite->getManagerClass(),
                    $manager->stop()
                );

                return $this;
            }
        };

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $manager = $this->buildChef();
        $manager->read($this->createMock(RecipeInterface::class));
        $manager->followSteps([new Bowl($middleware->execute(...), [])]);
        $manager->receiveRequest($clientMock, $serverRequestMock);
    }

    public function testStopWithMessage(): void
    {
        $middleware = new readonly class ($this) implements MiddlewareInterface {
            private \Teknoo\Tests\East\Foundation\Manager\ManagerTest $testSuite;

            public function __construct(ManagerTest $that)
            {
                $this->testSuite = $that;
            }

            public function execute(
                ClientInterface $client,
                MessageInterface $message,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $this->testSuite->assertInstanceOf(
                    $this->testSuite->getManagerClass(),
                    $manager->stop()
                );

                return $this;
            }
        };

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->buildChef();
        $manager->read($this->createMock(RecipeInterface::class));
        $manager->followSteps([new Bowl($middleware->execute(...), [])]);
        $manager->receiveRequest($clientMock, $messageMock);
    }

    public function testBehaviorReceiveRequest(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager): void {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps(
                [
                    new Bowl([$middleware1, 'execute'], []),
                    new Bowl([$middleware2, 'execute'], []),
                    new Bowl([$middleware3, 'execute'], [])
                ]
            );
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }

    public function testBehaviorReceiveMessage(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager): void {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($messagePassed, $messageMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps(
                [
                    new Bowl([$middleware1, 'execute'], []),
                    new Bowl([$middleware2, 'execute'], []),
                    new Bowl([$middleware3, 'execute'], [])
                ]
            );
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );
    }

    public function testBehaviorReceiveRequestAndContinueExecution(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $callList[] = 'middleware2';

                $managerPassed->continueExecution($clientPassed, $serverRequestMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        $this->assertSame(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveMessageAndContinueExecution(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager, &$callList, $middleware2) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($messagePassed, $messageMock);
                $callList[] = 'middleware2';

                $managerPassed->continueExecution($clientPassed, $messageMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        $this->assertSame(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveRequestAndUpdateMessage(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $callList[] = 'middleware2';

                $managerPassed->updateMessage($serverRequestMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        $this->assertSame(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveMessageAndUpdateMessage(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $callList = [];
        $middleware1->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use (&$callList, $middleware1) {
                $callList[] = 'middleware1';
                $managerPassed->stop();

                return $middleware1;
            }
        );
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager, &$callList, $middleware2) {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($messagePassed, $messageMock);
                $callList[] = 'middleware2';

                $managerPassed->updateMessage($messageMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        $this->assertSame(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorMultipleReceiveRequest(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->exactly(2))->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->exactly(2))->method('execute');
        $middleware2->expects($this->exactly(2))->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager): void {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($requestPassed, $serverRequestMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);

        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }

    public function testBehaviorMultipleReceiveMessage(): void
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->exactly(2))->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->exactly(2))->method('execute');
        $middleware2->expects($this->exactly(2))->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager): void {
                $this->assertEquals($clientPassed, $clientMock);
                $this->assertEquals($messagePassed, $messageMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);

        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        $this->assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );
    }
}
