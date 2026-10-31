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
 * Check of the streamed responses on FrankenPHP, run by the CI job `test:frankenphp` :
 *     php tests/frankenphp/check.php worker
 *     php tests/frankenphp/check.php classic
 * It starts `frankenphp php-server` with the test application of `tests/frankenphp/public/`, sends each request on a
 * socket, and checks the status, the headers, the body, and the time of each chunk. The generators of the test
 * application give a chunk each second : each chunk must arrive before the next chunk. It exits with 1 when a check
 * fails.
 */

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$mode = $argv[1] ?? '';
if ('worker' !== $mode && 'classic' !== $mode) {
    fwrite(STDERR, "Usage: php tests/frankenphp/check.php worker|classic\n");

    exit(2);
}

$port = 8080;
$root = __DIR__ . '/public';
$logFile = sys_get_temp_dir() . '/east-foundation-frankenphp-' . $mode . '.log';

$command = ['frankenphp', 'php-server', '--root', $root, '--listen', '127.0.0.1:' . $port];
if ('worker' === $mode) {
    //1 worker thread : the requests run one after the other in the same worker
    $command[] = '--worker';
    $command[] = $root . '/index.php,1';
}

$server = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $logFile, 'w'], 2 => ['file', $logFile, 'a']], $pipes);
if (false === $server) {
    fwrite(STDERR, "Error, FrankenPHP can not be started\n");

    exit(1);
}

$failures = 0;
$check = static function (bool $result, string $message) use (&$failures): void {
    if (true === $result) {
        echo "ok - $message\n";

        return;
    }

    ++$failures;
    echo "not ok - $message\n";
};

/*
 * Sends a GET request and reads the response. `$markers` are strings of the body : their time of arrival, in seconds
 * after the request, is kept. `$stopAt` closes the connection when it arrives.
 *
 * @return array{status: int, headers: array<string, string>, body: string, times: array<string, float>}
 */
$request = static function (string $path, array $markers = [], ?string $stopAt = null) use ($port): array {
    $socket = stream_socket_client('tcp://127.0.0.1:' . $port, $errorCode, $errorMessage, 5);
    if (false === $socket) {
        return ['status' => 0, 'headers' => [], 'body' => '', 'times' => []];
    }

    stream_set_timeout($socket, 15);
    fwrite($socket, "GET $path HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");

    $start = microtime(true);
    $data = '';
    $times = [];
    while (!feof($socket)) {
        $read = fread($socket, 8192);
        if (false === $read || ('' === $read && true === stream_get_meta_data($socket)['timed_out'])) {
            break;
        }

        $data .= $read;
        $now = microtime(true) - $start;

        foreach ($markers as $marker) {
            if (!isset($times[$marker]) && str_contains($data, $marker)) {
                $times[$marker] = $now;
            }
        }

        if (null !== $stopAt && str_contains($data, $stopAt)) {
            break;
        }
    }

    fclose($socket);

    $parts = explode("\r\n\r\n", $data, 2);
    $lines = explode("\r\n", $parts[0]);
    $status = (int) (explode(' ', $lines[0])[1] ?? 0);

    $headers = [];
    foreach (array_slice($lines, 1) as $line) {
        $header = explode(':', $line, 2);
        $headers[strtolower(trim($header[0]))] = trim($header[1] ?? '');
    }

    $body = $parts[1] ?? '';
    if ('chunked' === ($headers['transfer-encoding'] ?? '') && null === $stopAt) {
        $decoded = '';
        while ('' !== $body) {
            $sizeLine = explode("\r\n", $body, 2);
            $size = (int) hexdec($sizeLine[0]);
            if (0 === $size) {
                break;
            }

            $decoded .= substr($sizeLine[1] ?? '', 0, $size);
            $body = substr($sizeLine[1] ?? '', $size + 2);
        }

        $body = $decoded;
    }

    return ['status' => $status, 'headers' => $headers, 'body' => $body, 'times' => $times];
};

$ready = false;
for ($i = 0; $i < 100 && false === $ready; ++$i) {
    $socket = @stream_socket_client('tcp://127.0.0.1:' . $port, $errorCode, $errorMessage, 1);
    if (false !== $socket) {
        fclose($socket);
        $ready = true;

        break;
    }

    usleep(100_000);
}

$check($ready, "FrankenPHP listens on the port $port");

if (true === $ready) {
    $response = $request('/mode');
    $check($mode === $response['body'], "the test application runs in $mode mode");

    $response = $request('/chunks', ['chunk 1', 'chunk 2']);
    $times = $response['times'];
    $check(200 === $response['status'], '/chunks : the status is 200');
    //PHP adds the charset of `default_charset` to a text content type
    $check(
        str_starts_with($response['headers']['content-type'] ?? '', 'text/plain'),
        '/chunks : the content type is text/plain'
    );
    $check(
        0.9 <= ($times['chunk 1'] ?? 0.0) && 1.8 > ($times['chunk 1'] ?? 99.0),
        '/chunks : the chunk 1 arrives after 1 s, before the chunk 2'
    );
    $check(1.9 <= ($times['chunk 2'] ?? 0.0), '/chunks : the chunk 2 arrives after 2 s');
    $check('chunk 1chunk 2' === $response['body'], '/chunks : the body has the 2 chunks');

    //In the same worker, the next stream has its own headers
    $response = $request('/sse', ['message_start', 'keep-alive', 'chunk 3']);
    $times = $response['times'];
    $check(200 === $response['status'], '/sse : the status is 200');
    $check(
        str_starts_with($response['headers']['content-type'] ?? '', 'text/event-stream'),
        '/sse : the content type is text/event-stream'
    );
    $check(
        0.9 <= ($times['message_start'] ?? 0.0) && 1.8 > ($times['message_start'] ?? 99.0),
        '/sse : the event message_start arrives after 1 s, before the comment'
    );
    $check(
        1.9 <= ($times['keep-alive'] ?? 0.0) && 2.8 > ($times['keep-alive'] ?? 99.0),
        '/sse : the comment arrives after 2 s, before the last event'
    );
    $check(
        "event: message_start\ndata: {\"type\":\"message_start\"}\n\n: keep-alive\n\ndata: chunk 3\n\n"
            === $response['body'],
        '/sse : the body has the 3 server-sent events'
    );

    //The client closes the connection after the first chunk : the generator stops
    $id = bin2hex(random_bytes(8));
    $resultFile = sys_get_temp_dir() . '/east-foundation-stream-' . $id . '.json';
    $request('/disconnect?id=' . $id, [], 'chunk 1');

    $result = null;
    for ($i = 0; $i < 150 && null === $result; ++$i) {
        usleep(100_000);
        if (is_file($resultFile)) {
            $result = json_decode((string) file_get_contents($resultFile), true);
            unlink($resultFile);
        }
    }

    $check(is_array($result), '/disconnect : the generator stops after the close of the connection');
    if (is_array($result)) {
        echo "# /disconnect : {$result['chunks']} chunks before the stop\n";
        $check(50 > $result['chunks'], '/disconnect : the generator stops before its end');
    }

    $response = $request('/buffered');
    $check(200 === $response['status'], '/buffered : the status is 200');
    $check('15' === ($response['headers']['content-length'] ?? ''), '/buffered : the response has a content length');
    $check('a buffered body' === $response['body'], '/buffered : the body is complete');
}

proc_terminate($server);
proc_close($server);

if (0 < $failures) {
    echo "# $failures failed checks, log of FrankenPHP :\n";
    echo (string) file_get_contents($logFile);

    exit(1);
}

exit(0);
