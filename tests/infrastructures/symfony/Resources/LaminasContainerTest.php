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
use DI\ContainerBuilder;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Diactoros\ResponseMessageFactory;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LaminasContainerTest extends TestCase
{
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(
            __DIR__.'/../../../../infrastructures/symfony/config/laminas_di.php'
        );

        return $containerDefinition->build();
    }

    public function testServerRequestFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ServerRequestFactory::class,
            $container->get(ServerRequestFactory::class)
        );
    }

    public function testServerRequestFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ServerRequestFactoryInterface::class,
            $container->get(ServerRequestFactoryInterface::class)
        );
    }

    public function testUploadedFileFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            UploadedFileFactory::class,
            $container->get(UploadedFileFactory::class)
        );
    }

    public function testUploadedFileFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            UploadedFileFactoryInterface::class,
            $container->get(UploadedFileFactoryInterface::class)
        );
    }

    public function testResponseFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ResponseFactory::class,
            $container->get(ResponseFactory::class)
        );
    }

    public function testResponseFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ResponseFactoryInterface::class,
            $container->get(ResponseFactoryInterface::class)
        );
    }

    public function testStreamFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            StreamFactory::class,
            $container->get(StreamFactory::class)
        );
    }

    public function testStreamFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            StreamFactoryInterface::class,
            $container->get(StreamFactoryInterface::class)
        );
    }

    public function testMessageFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            MessageFactory::class,
            $container->get(MessageFactory::class)
        );
    }

    public function testResponseMessageFactory(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ResponseMessageFactory::class,
            $container->get(ResponseMessageFactory::class)
        );
    }
}
