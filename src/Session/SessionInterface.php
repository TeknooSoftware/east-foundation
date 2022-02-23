<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Session;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define object representing sessions in the request to allow developpers to share data between requests.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface SessionInterface
{
    final public const ATTRIBUTE_KEY = 'session';

    /**
     * Stores a given value in the session
     *
     * @param int|bool|string|float|array<mixed>|object|\JsonSerializable $value allows any nested combination
     *                                                                    of the previous types as well
     */
    public function set(string $key, mixed $value): SessionInterface;

    /*
     * Retrieves a value from the session
     */
    public function get(string $key, PromiseInterface $promise): SessionInterface;

    /*
     * Removes an item from the session
     */
    public function remove(string $key): SessionInterface;

    /*
     * Clears the contents of the session
     */
    public function clear(): SessionInterface;
}
