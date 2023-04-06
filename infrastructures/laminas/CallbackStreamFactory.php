<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Diactoros;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\Exception\CallbackStreamException;
use Teknoo\East\Foundation\Http\Message\CallbackStreamFactoryInterface;

use function fclose;
use function fopen;
use function stream_get_contents;

/**
 * Adapter of Laminas\Diactoros\CallbackStream for CallbackStreamInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CallbackStreamFactory implements StreamFactoryInterface, CallbackStreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return new CallbackStream(
            static fn(): string => $content
        );
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new CallbackStream(
            static function () use ($filename, $mode): string {
                $hF = @fopen($filename, $mode);
                if (!$hF) {
                    throw new CallbackStreamException("Can not open $filename");
                }

                $content = (string) stream_get_contents($hF);
                fclose($hF);

                return $content;
            }
        );
    }

    /**
     * @param resource $resource
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new CallbackStream(
            static fn(): string => (string) stream_get_contents($resource)
        );
    }
}
