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

namespace Teknoo\Tests\East\FoundationBunlde\Http;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Teknoo\East\FoundationBundle\Http\Client;

/**
 * Class ClientTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\FoundationBundle\Http\Client
 */
class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetResponseEvent
     */
    private $getResponseEvent;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @return GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getGetResponseEventMock(): GetResponseEvent
    {
        if (!$this->getResponseEvent instanceof GetResponseEvent) {
            $this->getResponseEvent = $this->createMock(GetResponseEvent::class);
        }

        return $this->getResponseEvent;
    }

    /**
     * @return HttpFoundationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getHttpFoundationFactoryMock(): HttpFoundationFactory
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        }

        return $this->httpFoundationFactory;
    }

    /**
     * @return Client
     */
    private function buildClient(): Client
    {
        return new Client($this->getHttpFoundationFactoryMock(), $this->getGetResponseEventMock());
    }

    /**
     * @return string
     */
    private function getClientClass(): string
    {
        return Client::class;
    }

    public function testResponseFromController()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $this->getGetResponseEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(function ($response) {
                return $response instanceof Response;
            }))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects(self::any())
            ->method('createResponse')
            ->with($this->callback(function ($response) {
                return $response instanceof ResponseInterface;
            }))
            ->willReturn($this->createMock(Response::class, [], [], '', false));

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->responseFromController($response)
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testResponseFromControllerWithoutGetResponseEvent()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock(ResponseInterface::class);

        $client = new Client($this->getHttpFoundationFactoryMock());
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->responseFromController($response)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testResponseFromControllerError()
    {
        $this->buildClient()->responseFromController(new \stdClass());
    }

    public function testErrorInRequest()
    {
        $this->getGetResponseEventMock()
            ->expects(self::any())
            ->method('setResponse')
            ->with($this->callback(function ($response) {
                return $response instanceof Response;
            }))
            ->willReturnSelf();

        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testErrorInRequestWithoutGetResponseEvent()
    {
        $client = new Client($this->getHttpFoundationFactoryMock());
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testErrorInRequestError()
    {
        $this->buildClient()->errorInRequest(new \stdClass());
    }

    public function testSetGetResponseEvent()
    {
        $client = $this->buildClient();
        self::assertInstanceOf(
            $this->getClientClass(),
            $client->setGetResponseEvent($this->createMock(GetResponseEvent::class))
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testSetGetResponseEventError()
    {
        $this->buildClient()->setGetResponseEvent(new \stdClass());
    }

    public function testClone()
    {
        $client = $this->buildClient();
        $clonedClient = clone $client;

        self::assertInstanceOf(Client::class, $clonedClient);

        $reflectionProperty = new \ReflectionProperty($clonedClient, 'httpFoundationFactory');
        $reflectionProperty->setAccessible(true);
        self::assertNotSame($this->getHttpFoundationFactoryMock(), $reflectionProperty->getValue($clonedClient));
    }
}
