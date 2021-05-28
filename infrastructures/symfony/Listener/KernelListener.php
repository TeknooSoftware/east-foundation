<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Listener;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\FoundationBundle\Http\ClientWithResponseEventInterface;

/**
 * Class KernelListener to listen the event "kernel.request" sent by Symfony and pass requests to the East Foundation's
 * manager to be processed. See http://symfony.com/doc/current/reference/events.html#kernel-request.
 *
 * It converts Symfony Request to PSR Request (East Foundation accepts use only PSR Request and Response).
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class KernelListener
{
    public function __construct(
        private ManagerInterface $manager,
        private ClientWithResponseEventInterface $client,
        private HttpMessageFactoryInterface $factory,
        private bool $clientInSilentMode = true,
    ) {
    }

    /**
     * To transform a symfony request as a psr request and inject the symfony request as attribute if the endpoint need
     * the symfony request.
     */
    private function getPsrRequest(Request $symfonyRequest): ServerRequestInterface
    {
        $psrRequest = $this->factory->createRequest($symfonyRequest);
        $psrRequest = $psrRequest->withAttribute('request', $symfonyRequest);

        return $psrRequest;
    }


    /**
     * To intercept a RequestEvent in the kernel loop to extract the request (if it's not an exception request) and
     * process it into East foundation.
     */
    public function onKernelRequest(RequestEvent $event): KernelListener
    {
        //To ignore sub request generated by symfony to handle non catch exception
        $request = $event->getRequest();
        if (!empty($request->attributes->has('exception'))) {
            return $this;
        }

        $client = clone $this->client;
        if (false === $this->clientInSilentMode) {
            $client->mustSendAResponse();
        }

        $client->setRequestEvent($event);

        $psrRequest = $this->getPsrRequest($event->getRequest());
        $this->manager->receiveRequest($client, $psrRequest);
        $client->sendResponse(null);

        return $this;
    }
}
