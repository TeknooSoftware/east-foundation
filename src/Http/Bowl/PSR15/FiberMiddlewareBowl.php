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

namespace Teknoo\East\Foundation\Http\Bowl\PSR15;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\RequestHandler\PSR15;
use Teknoo\Recipe\Bowl\FiberBowl;
use Teknoo\Recipe\ChefInterface;

/**
 * Recipe bowl to support PSR 15 middleware into a HTTP recipe.
 * PSR Response returned by the handler will be automatically passed to the client at the end of the execution.
 * The middleware will be executed into a dedicated fiber.
 * A proxy request handler is passed to the middleware, it will resume the recipe thanks to the manager and return
 * the PSR Response passed to the client to the middleware. (@see \Teknoo\East\Foundation\Http\RequestHandler\PSR15)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FiberMiddlewareBowl extends FiberBowl
{
    /**
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(
        MiddlewareInterface $middleware,
        array $mapping = [],
        string $name = '',
    ) {
        parent::__construct(
            callable: static function (
                ServerRequestInterface $request,
                ChefInterface $chef,
                ClientInterface $client,
            ) use ($middleware): void {
                $response = $middleware->process(
                    request: $request,
                    handler: new PSR15(
                        chef: $chef,
                        client: $client,
                    )
                );

                $client->acceptResponse($response);
            },
            name: $name,
            mapping: $mapping,
        );
    }
}
