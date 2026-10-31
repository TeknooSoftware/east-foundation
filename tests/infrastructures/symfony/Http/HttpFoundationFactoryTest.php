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

namespace Teknoo\Tests\East\FoundationBundle\Http;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\FoundationBundle\Http\HttpFoundationFactory;

use function ob_get_clean;
use function ob_start;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(HttpFoundationFactory::class)]
class HttpFoundationFactoryTest extends TestCase
{
    private function buildResponse(CallbackStreamInterface|Stream $body): Response
    {
        $response = new Response();
        $response = $response->withStatus(201);
        $response = $response->withHeader('content-type', 'text/event-stream');

        return $response->withBody($body);
    }

    private function sentContent(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    public function testCreateResponseWithAChunkStream(): void
    {
        $response = new HttpFoundationFactory()->createResponse(
            $this->buildResponse(
                new CallbackStream(static fn (): string => 'foo', CallbackStreamInterface::CHUNK_STREAM_TYPE)
            )
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('text/event-stream', $response->headers->get('content-type'));
        $this->assertSame('foo', $this->sentContent($response));
    }

    public function testCreateResponseWithASseStream(): void
    {
        $response = new HttpFoundationFactory()->createResponse(
            $this->buildResponse(
                new CallbackStream(static fn (): string => 'foo', CallbackStreamInterface::SSE_STREAM_TYPE)
            )
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('text/event-stream', $response->headers->get('content-type'));
        $this->assertSame('foo', $this->sentContent($response));
    }

    public function testCreateResponseWithACallbackStream(): void
    {
        $response = new HttpFoundationFactory()->createResponse(
            $this->buildResponse(
                new CallbackStream(static fn (): string => 'foo')
            )
        );

        $this->assertNotInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('foo', $response->getContent());
    }

    public function testCreateResponseWithAnotherStream(): void
    {
        $body = new Stream('php://memory', 'rw');
        $body->write('bar');

        $response = new HttpFoundationFactory()->createResponse(
            $this->buildResponse($body)
        );

        $this->assertNotInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('bar', $response->getContent());
    }

    public function testCreateStreamedResponseOnDemand(): void
    {
        $response = new HttpFoundationFactory()->createResponse(
            $this->buildResponse(
                new CallbackStream(static fn (): string => 'foo')
            ),
            true,
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame('foo', $this->sentContent($response));
    }
}
