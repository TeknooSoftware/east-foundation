<?php
/**
 * East Framework.
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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Framework\Http;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\East\Framework\Http\Client\Client;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class ClientTest
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers Teknoo\East\Framework\Http\Client\Client
 * @covers Teknoo\East\Framework\Http\Client\States\Error
 * @covers Teknoo\East\Framework\Http\Client\States\Pending
 * @covers Teknoo\East\Framework\Http\Client\States\Success
 */
class ClientTest extends \PHPUnit_Framework_TestCase
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
            $this->getResponseEvent = $this->createMock(
                'Symfony\Component\HttpKernel\Event\GetResponseEvent',
                [],
                [],
                '',
                false
            );
        }

        return $this->getResponseEvent;
    }

    /**
     * @return HttpFoundationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getHttpFoundationFactoryMock(): HttpFoundationFactory
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createMock(
                'Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory',
                [],
                [],
                '',
                false
            );
        }

        return $this->httpFoundationFactory;
    }

    /**
     * @return Client
     */
    private function buildClient(): Client
    {
        return new Client($this->getGetResponseEventMock(), $this->getHttpFoundationFactoryMock());
    }

    /**
     * @return string
     */
    private function getClientClass(): string
    {
        return 'Teknoo\East\Framework\Http\Client';
    }

    public function testSuccessfulResponseFromController()
    {
        /**
         * @var ResponseInterface
         */
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->getGetResponseEventMock()
            ->expects($this->any())
            ->method('setResponse')
            ->with($this->callback(function($response){ return $response instanceof Response; }))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects($this->any())
            ->method('createResponse')
            ->with($this->callback(function($response){ return $response instanceof ResponseInterface; }))
            ->willReturn($this->createMock('Symfony\Component\HttpFoundation\Response', [], [], '', false));

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->successfulResponseFromController($response)
        );
        $this->assertTrue($client->hasSuccessFull());
    }

    /**
     * @expectedException \TypeError
     */
    public function testSuccessfulResponseFromControllerError()
    {
        $this->buildClient()->successfulResponseFromController(new \stdClass());
    }

    public function testErrorInRequest()
    {
        $this->getGetResponseEventMock()
            ->expects($this->any())
            ->method('setResponse')
            ->with($this->callback(function($response){ return $response instanceof Response; }))
            ->willReturnSelf();

        $client = $this->buildClient();
        $this->assertInstanceOf(
            $this->getClientClass(),
            $client->errorInRequest(new \Exception('fooBar'))
        );
        $this->assertFalse($client->hasSuccessFull());
    }

    /**
     * @expectedException \TypeError
     */
    public function testErrorInRequestError()
    {
        $this->buildClient()->errorInRequest(new \stdClass());
    }
}
