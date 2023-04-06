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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\FoundationBundle\Resources;

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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LaminasContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(
        __DIR__.'/../../../../infrastructures/symfony/Resources/config/laminas_di.php'
        );

        return $containerDefinition->build();
    }

    public function testServerRequestFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          ServerRequestFactory::class,
            $container->get(ServerRequestFactory::class)
        );
    }

    public function testServerRequestFactoryInterface()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          ServerRequestFactoryInterface::class,
            $container->get(ServerRequestFactoryInterface::class)
        );
    }

    public function testUploadedFileFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          UploadedFileFactory::class,
            $container->get(UploadedFileFactory::class)
        );
    }

    public function testUploadedFileFactoryInterface()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          UploadedFileFactoryInterface::class,
            $container->get(UploadedFileFactoryInterface::class)
        );
    }

    public function testResponseFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          ResponseFactory::class,
            $container->get(ResponseFactory::class)
        );
    }

    public function testResponseFactoryInterface()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          ResponseFactoryInterface::class,
            $container->get(ResponseFactoryInterface::class)
        );
    }

    public function testStreamFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          StreamFactory::class,
            $container->get(StreamFactory::class)
        );
    }

    public function testStreamFactoryInterface()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          StreamFactoryInterface::class,
            $container->get(StreamFactoryInterface::class)
        );
    }

    public function testMessageFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
          MessageFactory::class,
            $container->get(MessageFactory::class)
        );
    }

    public function testResponseMessageFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            ResponseMessageFactory::class,
            $container->get(ResponseMessageFactory::class)
        );
    }
}
