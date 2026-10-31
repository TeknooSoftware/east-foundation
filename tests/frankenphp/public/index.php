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

/*
 * Test application of the CI job `test:frankenphp`, run by `tests/frankenphp/check.php` in worker mode and in classic
 * mode. It sends the responses with the Symfony `Client` of the bundle, its `HttpFoundationFactory` and the streams of
 * `CallbackStreamFactory`, with the real PHP functions.
 */

use Laminas\Diactoros\Response as LaminasResponse;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\Message\ChunkGeneratorInterface;
use Teknoo\East\Foundation\Http\Message\SseGeneratorInterface;
use Teknoo\East\FoundationBundle\Http\Client;
use Teknoo\East\FoundationBundle\Http\HttpFoundationFactory;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

$mode = 'classic';
if (
    function_exists('frankenphp_handle_request')
    && filter_var($_SERVER['FRANKENPHP_WORKER'] ?? false, FILTER_VALIDATE_BOOL)
) {
    $mode = 'worker';
}

$kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());

$handler = static function () use ($kernel, $mode): void {
    $request = Request::createFromGlobals();
    $streamFactory = new CallbackStreamFactory();
    $response = new LaminasResponse();
    $response = $response->withHeader('content-type', 'text/plain');

    $path = $request->getPathInfo();
    if ('/mode' === $path) {
        $response = $response->withBody($streamFactory->createStream($mode));
    }

    //A chunk each second
    if ('/chunks' === $path) {
        $generator = new class () implements ChunkGeneratorInterface {
            public function getIterator(): Generator
            {
                foreach (['chunk 1', 'chunk 2'] as $chunk) {
                    sleep(1);

                    yield $chunk;
                }
            }
        };

        $response = $response->withBody($streamFactory->createStreamFromGenerator($generator));
    }

    //An event, a comment, then an event without name, each second
    if ('/sse' === $path) {
        $generator = new class () implements SseGeneratorInterface {
            public function getIterator(): Generator
            {
                sleep(1);
                yield 'message_start' => '{"type":"message_start"}';
                sleep(1);
                yield SseGeneratorInterface::COMMENT => 'keep-alive';
                sleep(1);
                yield 'chunk 3';
            }
        };

        $response = $response->withHeader('content-type', 'text/event-stream');
        $response = $response->withBody($streamFactory->createStreamFromGenerator($generator));
    }

    //A chunk each 0.2 s, up to 50 chunks : the number of chunks goes to a file when the generator stops
    if ('/disconnect' === $path) {
        $resultFile = sys_get_temp_dir() . '/east-foundation-stream-' . $request->query->getAlnum('id') . '.json';
        $generator = new class ($resultFile) implements ChunkGeneratorInterface {
            public function __construct(
                private readonly string $resultFile,
            ) {
            }

            public function getIterator(): Generator
            {
                $count = 0;
                try {
                    for ($i = 1; $i <= 50; ++$i) {
                        ++$count;

                        yield "chunk $i\n";

                        usleep(200_000);
                    }
                } finally {
                    file_put_contents($this->resultFile, json_encode(['chunks' => $count]));
                }
            }
        };

        $response = $response->withBody($streamFactory->createStreamFromGenerator($generator));
    }

    if ('/buffered' === $path) {
        $response = $response->withBody($streamFactory->createStream('a buffered body'));
    }

    $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    $client = new Client(new HttpFoundationFactory(), new ResponseFactory(), new StreamFactory(), $event);
    $client->sendResponse($response);

    $event->getResponse()?->send();
};

if ('worker' === $mode) {
    while (frankenphp_handle_request($handler)) {
        gc_collect_cycles();
    }

    return;
}

$handler();
