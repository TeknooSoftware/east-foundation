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

namespace Teknoo\East\Framework\Http\Client\States;

use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Framework\Http\ClientInterface;
use Teknoo\States\State\AbstractState;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

class Pending extends AbstractState
{
    /**
     * {@inheritdoc}
     */
    private function doSuccessfulResponseFromController(ResponseInterface $response): ClientInterface
    {
        $this->getResponseEvent->setResponse(
            $this->httpFoundationFactory->createResponse($response)
        );

        $this->switchState('Success');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function doErrorInRequest(\Throwable $throwable): ClientInterface
    {
        $this->getResponseEvent->setResponse(
            new Response(
                $throwable->getMessage(),
                500
            )
        );

        $this->switchState('Error');

        return $this;
    }
}