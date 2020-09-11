<?php
/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\Resources;

use DI\Container;
use DI\ContainerBuilder;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
}
