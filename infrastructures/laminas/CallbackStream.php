<?php

/**
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Diactoros;

use Laminas\Diactoros\CallbackStream as DiactorosCallbackStream;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;

/**
 * Adapter of Laminas\Diactoros\CallbackStream for CallbackStreamInterface
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CallbackStream extends DiactorosCallbackStream implements CallbackStreamInterface
{
    /**
     * @inheritDoc
     */
    public function bind(callable $callback): CallbackStreamInterface
    {
        $this->attach($callback);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unbind(): CallbackStreamInterface
    {
        $this->detach();

        return $this;
    }
}
