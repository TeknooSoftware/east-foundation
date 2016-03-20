<?php

namespace Teknoo\East\Framework\Listener;

use Teknoo\East\Framework\Http\Client;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Teknoo\East\Framework\Manager\ManagerInterface;

/**
 * Class KernelListener
 * @package Teknoo\East\Framework
 */
class KernelListener
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * KernelListener constructor.
     * @param ManagerInterface $manager
     * @param HttpFoundationFactory $httpFactory
     * @param DiactorosFactory $diactorosFactory
     */
    public function __construct(
        ManagerInterface $manager,
        HttpFoundationFactory $httpFactory,
        DiactorosFactory $diactorosFactory
    ) {
        $this->manager = $manager;
        $this->httpFoundationFactory = $httpFactory;
        $this->diactorosFactory = $diactorosFactory;
    }

    /**
     * @param GetResponseEvent $event
     * @return KernelListener
     */
    public function onKernelRequest(GetResponseEvent $event): KernelListener
    {
        $client = new Client($event, $this->httpFoundationFactory);
        $this->manager->receiveRequestFromClient(
            $client,
            $this->diactorosFactory->createRequest(
                $event->getRequest()
            )
        );

        return $this;
    }
}