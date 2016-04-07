<?php

namespace Teknoo\Tests\East\Framework\Http;

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
     * @return GetResponseEvent
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
     * @return HttpFoundationFactory
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
}