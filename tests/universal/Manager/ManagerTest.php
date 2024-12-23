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

namespace Teknoo\Tests\East\Foundation\Manager;

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
use Teknoo\Tests\Recipe\AbstractChefTests;

/**
 * Class ManagerTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Manager::class)]
class ManagerTest extends AbstractChefTests
{
    /**
     * @return ChefInterface|ManagerInterface
     */
    public function buildChef(): ChefInterface
    {
        return new Manager();
    }

    public function testReadInConstructor()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ManagerInterface::class,
            new Manager($recipe)
        );
    }

    /**
     * @return string
     */
    public function getManagerClass(): string
    {
        return Manager::class;
    }

    public function testReceiveRequest()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildChef()->read($this->createMock(RecipeInterface::class))
                ->followSteps([$this->createMock(BowlInterface::class)])
                ->receiveRequest(
                    $this->createMock(ClientInterface::class),
                    $this->createMock(ServerRequestInterface::class)
                )
        );
    }

    public function testReceiveMessage()
    {
        self::assertInstanceOf(
            $this->getManagerClass(),
            $this->buildChef()->read($this->createMock(RecipeInterface::class))
                ->followSteps([$this->createMock(BowlInterface::class)])
                ->receiveRequest(
                    $this->createMock(ClientInterface::class),
                    $this->createMock(MessageInterface::class)
                )
        );
    }

    public function testReceiveRequestErrorClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->receiveRequest(
                new \stdClass(),
                $this->createMock(ServerRequestInterface::class)
            );
    }

    public function testReceiveRequestErrorRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->receiveRequest(
                $this->createMock(ClientInterface::class),
                new \stdClass()
            );
    }

    public function testContinueExecutionErrorClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->continueExecution(
                new \stdClass(),
                $this->createMock(ServerRequestInterface::class)
            );
    }

    public function testContinueExecutionErrorRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->continueExecution(
                $this->createMock(ClientInterface::class),
                new \stdClass()
            );
    }

    public function testUpdateMessageExecutionErrorRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->followSteps([$this->createMock(BowlInterface::class)])
            ->updateWorkPlan(
                new \stdClass()
            );
    }

    public function testStopWithRequest()
    {
        $middleware = new class($this) implements MiddlewareInterface {
            /**
             * @var ManagerTest
             */
            private $testSuite;

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
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $manager = $this->buildChef();
        $manager->read($this->createMock(RecipeInterface::class));
        $manager->followSteps([new Bowl([$middleware, 'execute'], [])]);
        $manager->receiveRequest($clientMock, $serverRequestMock);
    }

    public function testStopWithMessage()
    {
        $middleware = new class($this) implements MiddlewareInterface {
            /**
             * @var ManagerTest
             */
            private $testSuite;

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
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        $manager = $this->buildChef();
        $manager->read($this->createMock(RecipeInterface::class));
        $manager->followSteps([new Bowl([$middleware, 'execute'], [])]);
        $manager->receiveRequest($clientMock, $messageMock);
    }

    public function testBehaviorReceiveRequest()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }

    public function testBehaviorReceiveMessage()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($messagePassed, $messageMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );
    }

    public function testBehaviorReceiveRequestAndContinueExecution()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                $callList[] = 'middleware2';

                $managerPassed->continueExecution($clientPassed, $serverRequestMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveMessageAndContinueExecution()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($messagePassed, $messageMock);
                $callList[] = 'middleware2';

                $managerPassed->continueExecution($clientPassed, $messageMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveRequestAndUpdateMessage()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                $callList[] = 'middleware2';

                $managerPassed->updateMessage($serverRequestMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorReceiveMessageAndUpdateMessage()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
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
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())->method('execute');
        $middleware2->expects($this->once())->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager, &$callList, $middleware2) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($messagePassed, $messageMock);
                $callList[] = 'middleware2';

                $managerPassed->updateMessage($messageMock);

                return $middleware2;
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);
        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        self::assertEquals(['middleware2','middleware1'], $callList);
    }

    public function testBehaviorMultipleReceiveRequest()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->exactly(2))->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->exactly(2))->method('execute');
        $middleware2->expects($this->exactly(2))->method('execute')->willReturnCallback(
            function ($clientPassed, $requestPassed, $managerPassed) use ($clientMock, $serverRequestMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($requestPassed, $serverRequestMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $serverRequestMock)
        );
    }
    
    public function testBehaviorMultipleReceiveMessage()
    {
        $manager = $this->buildChef();

        /**
         * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $clientMock = $this->createMock(ClientInterface::class);

        /**
         * @var MessageInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $messageMock = $this->createMock(MessageInterface::class);

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->exactly(2))->method('execute')->willReturnSelf();
        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->exactly(2))->method('execute');
        $middleware2->expects($this->exactly(2))->method('execute')->willReturnCallback(
            function ($clientPassed, $messagePassed, $managerPassed) use ($clientMock, $messageMock, $manager) {
                self::assertEquals($clientPassed, $clientMock);
                self::assertEquals($messagePassed, $messageMock);
                $managerPassed->stop();
            }
        );

        /**
         * @var MiddlewareInterface|\PHPUnit\Framework\MockObject\MockObject
         */
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $middleware3->expects($this->never())->method('execute');

        $manager->read($this->createMock(RecipeInterface::class))
            ->followSteps([
                new Bowl([$middleware1, 'execute'], []),
                new Bowl([$middleware2, 'execute'], []),
                new Bowl([$middleware3, 'execute'], [])
            ]);

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );

        self::assertInstanceOf(
            $this->getManagerClass(),
            $manager->receiveRequest($clientMock, $messageMock)
        );
    }
}
