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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\FoundationBundle\Router;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\FoundationBundle\Router\Router;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Zend\Diactoros\Uri;

/**
 * Class RouterTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Router\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @return UrlMatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUrlMatcherMock(): UrlMatcherInterface
    {
        if (!$this->matcher instanceof UrlMatcherInterface) {
            $this->matcher = $this->createMock(UrlMatcherInterface::class);
        }

        return $this->matcher;
    }

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerMock(): ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            $this->container = $this->createMock(ContainerInterface::class);
        }

        return $this->container;
    }

    /**
     * @return ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProcessorMock(): ProcessorInterface
    {
        if (!$this->processor instanceof ProcessorInterface) {
            $this->processor = $this->createMock(ProcessorInterface::class);
        }

        return $this->processor;
    }

    /**
     * @return Router
     */
    private function buildRouter(): Router
    {
        return new Router(
            $this->getUrlMatcherMock(),
            $this->getContainerMock(),
            $this->getProcessorMock()
        );
    }

    /**
     * @return string
     */
    private function getRouterClass(): string
    {
        return Router::class;
    }

    public function testReceiveRequestFromServerNotFound()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn([]);

        $this->getProcessorMock()->expects(self::never())->method('executeRequest');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerNotFoundException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willThrowException(new ResourceNotFoundException());

        $this->getProcessorMock()->expects(self::never())->method('executeRequest');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testReceiveRequestFromServerOtherException()
    {
        /**
         * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation');

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willThrowException(new \Exception());

        $this->getProcessorMock()->expects(self::never())->method('executeRequest');

        $this->buildRouter()->receiveRequestFromServer($client, $request, $manager);
    }

    public function testReceiveRequestFromServerWithNoController()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['foo', 'bar']);

        $this->getProcessorMock()->expects(self::never())->method('executeRequest');

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerWithControllerNotCallable()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'foo::bar']);

        $this->getProcessorMock()->expects(self::never())->method('executeRequest')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerWithControllerInContainerNotCallable()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects(self::any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects(self::any())->method('get')->with('fooBar')->willReturn('foo::bar');


        $this->getProcessorMock()->expects(self::never())->method('executeRequest')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerWithController()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => function () {
        }]);

        $this->getProcessorMock()->expects(self::once())->method('executeRequest')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    public function testReceiveRequestFromServerWithControllerInCOntainer()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
         */
        $client = $this->createMock(ClientInterface::class);
        /**
         * @var ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject $request
         */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn(new class() extends Uri {
        });
        /**
         * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $manager
         */
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('stopPropagation')->willReturnSelf();

        $this->getUrlMatcherMock()->expects(self::any())->method('match')->willReturn(['_controller' => 'fooBar']);

        $this->getContainerMock()->expects(self::any())->method('has')->with('fooBar')->willReturn(true);
        $this->getContainerMock()->expects(self::any())->method('get')->with('fooBar')->willReturn(function () {
        });

        $this->getProcessorMock()->expects(self::once())->method('executeRequest')->willReturnSelf();

        self::assertInstanceOf(
            $this->getRouterClass(),
            $this->buildRouter()->receiveRequestFromServer($client, $request, $manager)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorClient()
    {
        $this->buildRouter()->receiveRequestFromServer(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorRequest()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testReceiveRequestFromServerErrorManager()
    {
        $this->buildRouter()->receiveRequestFromServer(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }
}
