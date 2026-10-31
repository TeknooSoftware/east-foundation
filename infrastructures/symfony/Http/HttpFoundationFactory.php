<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Http;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory as SymfonyHttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;

/**
 * Factory to convert a PSR-7 response into a Symfony response. A body with the metadata `stream_type` at
 * `CallbackStreamInterface::CHUNK_STREAM_TYPE` or `CallbackStreamInterface::SSE_STREAM_TYPE` gives a Symfony
 * `StreamedResponse`: the body sends its chunks one by one, after the status and the headers. Another body gives the
 * same response as the Symfony factory.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class HttpFoundationFactory extends SymfonyHttpFoundationFactory
{
    public function createResponse(ResponseInterface $psrResponse, bool $streamed = false): Response
    {
        $streamType = $psrResponse->getBody()->getMetadata('stream_type');

        if (CallbackStreamInterface::CHUNK_STREAM_TYPE === $streamType) {
            $streamed = true;
        }

        if (CallbackStreamInterface::SSE_STREAM_TYPE === $streamType) {
            $streamed = true;
        }

        return parent::createResponse($psrResponse, $streamed);
    }
}
