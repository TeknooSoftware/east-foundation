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

namespace Teknoo\Tests\East\FoundationBundle\Resources;

use PHPUnit\Framework\TestCase;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Client\ClientInterface as BaseClient;
use Teknoo\East\Foundation\Http\ClientInterface as HttpClient;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\FoundationBundle\Http\Client;
use Teknoo\East\FoundationBundle\Session\SessionMiddleware;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    protected function buildContainer(): Container
    {
        return include __DIR__.'/../../../../infrastructures/symfony/generator.php';
    }

    public function testCreateManager(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        $this->assertInstanceOf(
            Manager::class,
            $manager1
        );

        $this->assertInstanceOf(
            Manager::class,
            $manager2
        );

        $this->assertSame($manager1, $manager2);
    }

    public function testCreateProcessor(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $manager = $this->createMock(ManagerInterface::class);
        $container->set(ManagerInterface::class, $manager);
        $processor1 = $container->get(ProcessorInterface::class);
        $processor2 = $container->get(Processor::class);

        $this->assertInstanceOf(
            Processor::class,
            $processor1
        );

        $this->assertInstanceOf(
            Processor::class,
            $processor2
        );

        $this->assertSame($processor1, $processor2);
    }

    public function testCreateClient(): void
    {
        $container = $this->buildContainer();
        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $manager = $this->createMock(ManagerInterface::class);
        $container->set(ManagerInterface::class, $manager);
        $client1 = $container->get(BaseClient::class);
        $client2 = $container->get(HttpClient::class);
        $client3 = $container->get(Client::class);

        $this->assertInstanceOf(
            Client::class,
            $client1
        );

        $this->assertInstanceOf(
            Client::class,
            $client2
        );

        $this->assertInstanceOf(
            Client::class,
            $client3
        );

        $this->assertSame($client1, $client3);
        $this->assertSame($client2, $client3);
    }

    public function testCreateSessionMiddleware(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            SessionMiddleware::class,
            $container->get(SessionMiddleware::class)
        );
    }
}
