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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\FoundationBundle\Listener;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\FoundationBundle\Http\ClientWithResponseEventInterface;

/**
 * Class KernelListener to listen the event "kernel.request" sent by Symfony and pass requests to the East Foundation's
 * manager to be processed. See http://symfony.com/doc/current/reference/events.html#kernel-request.
 *
 * It converts Symfony Request to PSR Request (East Foundation accepts use only PSR Request and Response).
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class KernelListener
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var ClientWithResponseEventInterface
     */
    private $clientWithResponseEvent;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * KernelListener constructor.
     *
     * @param ManagerInterface      $manager
     * @param ClientWithResponseEventInterface $clientWithResponseEvent
     * @param DiactorosFactory      $diactorosFactory
     */
    public function __construct(
        ManagerInterface $manager,
        ClientWithResponseEventInterface $clientWithResponseEvent,
        DiactorosFactory $diactorosFactory
    ) {
        $this->manager = $manager;
        $this->clientWithResponseEvent = $clientWithResponseEvent;
        $this->diactorosFactory = $diactorosFactory;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return KernelListener
     */
    public function onKernelRequest(GetResponseEvent $event): KernelListener
    {
        $this->clientWithResponseEvent->setGetResponseEvent($event);
        $this->manager->receiveRequestFromClient(
            $this->clientWithResponseEvent,
            $this->diactorosFactory->createRequest(
                $event->getRequest()
            )
        );

        return $this;
    }
}
