<?php

/**
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

namespace Teknoo\Tests\East\Foundation\Http\RequestHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\RequestHandler\Exception\MissingResponseException;
use Teknoo\East\Foundation\Http\RequestHandler\PSR15;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MissingResponseException::class)]
#[CoversClass(PSR15::class)]
class PSR15Test extends TestCase
{
    public function testHandle(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $client
            ->method('updateResponse')
            ->willReturnCallback(
                function (callable $callback) use ($client): \Teknoo\East\Foundation\Client\ClientInterface {
                    $callback($client, $this->createMock(ResponseInterface::class));

                    return $client;
                }
            );

        $handler = new PSR15($chef, $client);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $handler->handle($request),
        );
    }

    public function testErrorHandleWithoutResponse(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $handler = new PSR15($chef, $client);

        $this->expectException(MissingResponseException::class);
        $handler->handle($request);
    }
}
