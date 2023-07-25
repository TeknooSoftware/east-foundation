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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Http\Bowl\PSR15;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\Recipe\Bowl\Bowl;

/**
 * Recipe bowl to support PSR 15 Request handler into a HTTP recipe.
 * PSR Response returned by the handler will be automatically passed to the client at the end of the execution
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class HandlerBowl extends Bowl
{
    /**
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(
        RequestHandlerInterface $handler,
        array $mapping = [],
        string $name = '',
    ) {
        parent::__construct(
            callable: static function (
                ServerRequestInterface $request,
                ClientInterface $client,
            ) use ($handler) {
                $response = $handler->handle(
                    request: $request
                );

                $client->acceptResponse($response);
            },
            name: $name,
            mapping: $mapping,
        );
    }
}
