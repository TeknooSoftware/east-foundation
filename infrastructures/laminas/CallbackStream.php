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

namespace Teknoo\East\Diactoros;

use Laminas\Diactoros\CallbackStream as DiactorosCallbackStream;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;

use function is_array;

/**
 * Adapter of Laminas\Diactoros\CallbackStream for CallbackStreamInterface
 *
 * The metadata `stream_type` is `callback`, or the type given to the constructor
 * (`CallbackStreamInterface::CHUNK_STREAM_TYPE` or `CallbackStreamInterface::SSE_STREAM_TYPE` for a stream of a
 * generator).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CallbackStream extends DiactorosCallbackStream implements CallbackStreamInterface
{
    public function __construct(
        callable $callback,
        private readonly string $streamType = 'callback',
    ) {
        parent::__construct($callback);
    }

    public function getMetadata(?string $key = null): mixed
    {
        if ('stream_type' === $key) {
            return $this->streamType;
        }

        $metadata = parent::getMetadata($key);
        if (is_array($metadata)) {
            $metadata['stream_type'] = $this->streamType;
        }

        return $metadata;
    }

    public function bind(callable $callback): CallbackStreamInterface
    {
        $this->attach($callback);

        return $this;
    }

    public function unbind(): CallbackStreamInterface
    {
        $this->detach();

        return $this;
    }
}
