<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\Foundation\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * ClientInterface is a contract to create object representing the client in the server side. The client must be
 * agnostic and accepts only \Throwable exception and PSR7 response. It's possible to pass a PSR7 Response object
 * without send it via the method "acceptResponse".
 *
 * To update an response, it's mandatory to call the method
 * "updateResponse" and pass a callable able to update the response and update it into the client.
 *
 * The method "sendResponse" as a behavior like updateResponse but send directly the response.
 *
 * All public method of the client must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ClientInterface
{
    /**
     * To update a response in the client. You must pass a callable able to work on the response passed into argument,
     * and repush it into the client via its method "acceptResponse". (Response object are immutable).
     * The callable must accept a ClientInterface object as first argument, and a PSR7 Response as second (can be null).
     *
     * @param callable $modifier
     *
     * @return ClientInterface
     */
    public function updateResponse(callable $modifier): ClientInterface;

    /**
     * To accept a response from the controller action without send it to the HTTP client.
     *
     * @param ResponseInterface $response
     *
     * @return ClientInterface
     */
    public function acceptResponse(ResponseInterface $response): ClientInterface;

    /**
     * To accept a response from the controller action and send it to the HTTP client.
     *
     * @param ResponseInterface|null $response
     * @param bool $silently=false
     *
     * @return ClientInterface
     * @throws \RuntimeException when no response was been defined via acceptResponse and $response argument is null.
     */
    public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface;

    /**
     * To intercept an error during a request and forward the message to the HTTP client.
     *
     * @param \Throwable $throwable
     *
     * @return ClientInterface
     */
    public function errorInRequest(\Throwable $throwable): ClientInterface;
}
