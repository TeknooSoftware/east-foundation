<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Client;

use Psr\Http\Message\MessageInterface;
use RuntimeException;
use SensitiveParameter;
use Throwable;

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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ClientInterface
{
    /*
     * To update a response in the client. You must pass a callable able to work on the response passed into argument,
     * and repush it into the client via its method "acceptResponse". (Response object are immutable).
     * The callable must accept a ClientInterface object as first argument, and a the Response as second (can be null).
     */
    public function updateResponse(callable $modifier): ClientInterface;

    /*
     * To accept a response from the controller action without send it to the client.
     */
    public function acceptResponse(ResponseInterface | MessageInterface $response): ClientInterface;

    /**
     * To accept a response from the controller action and send it to the client.
     *
     * @throws RuntimeException when no response was been defined via acceptResponse and $response argument is null.
     */
    public function sendResponse(
        ResponseInterface | MessageInterface | null $response = null,
        bool $silently = false
    ): ClientInterface;

    /**
     * To intercept an error during a request and forward the message to the client.
     *
     * @param bool $silently To ask client to not throw the exception (execute throw $throwable) or not. Default false
     */
    public function errorInRequest(#[SensitiveParameter] Throwable $throwable, bool $silently = false): ClientInterface;

    /**
     * To set the client to be not silent, by default, when it will send a missing response and throw an exception
     */
    public function mustSendAResponse(): ClientInterface;

    /**
     * To set the client to be silent, by default, when it will send a missing response and throw an exception
     */
    public function sendAResponseIsOptional(): ClientInterface;
}
