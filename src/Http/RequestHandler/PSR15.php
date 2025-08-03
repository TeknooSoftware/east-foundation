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

namespace Teknoo\East\Foundation\Http\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\RequestHandler\Exception\MissingResponseException;
use Teknoo\Recipe\ChefInterface;

/**
 * PSR15 Request handler passed to middleware to resume the HTTP recipe thanks to the manager, and extract from the
 * client the accepted response. If the response is not available, this handler will throw the exception
 * MissingResponseException.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PSR15 implements RequestHandlerInterface
{
    private ?ResponseInterface $extractedResponse = null;

    public function __construct(
        private readonly ChefInterface $chef,
        private readonly ClientInterface $client,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->chef->continue();

        $this->client->updateResponse(
            function (ClientInterface $client, $response): void {
                if ($response instanceof ResponseInterface) {
                    $this->extractedResponse = $response;
                }
            }
        );

        if (null === $this->extractedResponse) {
            throw new MissingResponseException("The response was not pushed to the client", 500);
        }

        return $this->extractedResponse;
    }
}
