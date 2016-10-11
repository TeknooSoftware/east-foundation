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
namespace Teknoo\East\Framework\Http\Client\States;

use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Framework\Http\Client\Client;
use Teknoo\East\Framework\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @mixin Client
 */
class Pending implements StateInterface
{
    use StateTrait;

    private function doSuccessfulResponseFromController()
    {
        /**
         * To accept a response from the controller action and send it to the HTTP client.
         *
         * @param ResponseInterface $response
         *
         * @return ClientInterface
         */
        return function (ResponseInterface $response): ClientInterface {
            $this->getResponseEvent->setResponse(
                $this->httpFoundationFactory->createResponse($response)
            );

            $this->switchState(Success::class);

            return $this;
        };
    }

    public function doErrorInRequest()
    {
        /**
         * To intercept an error during a request and forward the message to the HTTP client.
         *
         * @param \Throwable $throwable
         *
         * @return ClientInterface
         */
        return function (\Throwable $throwable): ClientInterface {
            $this->getResponseEvent->setResponse(
                new Response(
                    $throwable->getMessage(),
                    500
                )
            );

            $this->switchState(Error::class);

            return $this;
        };
    }
}
