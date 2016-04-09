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
 * to contact@uni-alteri.com so we can send you a copy immediately.
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
use Teknoo\East\Framework\Http\Client;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class ClientTest
 * @package Teknoo\Tests\East\Framework\Http
 * @covers Teknoo\East\Framework\Http\Client
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
            $this->getResponseEvent = $this->getMock(
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
            $this->httpFoundationFactory = $this->getMock(
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
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');

        $this->getGetResponseEventMock()
            ->expects($this->any())
            ->method('setResponse')
            ->with($this->callback(function($response){ return $response instanceof Response; }))
            ->willReturnSelf();

        $this->getHttpFoundationFactoryMock()
            ->expects($this->any())
            ->method('createResponse')
            ->with($this->callback(function($response){ return $response instanceof ResponseInterface; }))
            ->willReturn($this->getMock('Symfony\Component\HttpFoundation\Response', [], [], '', false));

        $this->assertInstanceOf(
            $this->getClientClass(),
            $this->buildClient()->successfulResponseFromController($response)
        );
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

        $this->assertInstanceOf(
            $this->getClientClass(),
            $this->buildClient()->errorInRequest(new \Exception('fooBar'))
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testErrorInRequestError()
    {
        $this->buildClient()->errorInRequest(new \stdClass());
    }
}