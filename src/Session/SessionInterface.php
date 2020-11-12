<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Session;

use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * Interface to define object representing sessions in the request to allow developpers to share data betweens requests.
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface SessionInterface
{
    public const ATTRIBUTE_KEY = 'session';

    /**
     * Stores a given value in the session
     *
     * @param string                                               $key
     * @param int|bool|string|float|array<mixed>|object|\JsonSerializable $value allows any nested combination
     *                                                                    of the previous types as well
     *
     * @return SessionInterface
     */
    public function set(string $key, $value): SessionInterface;

    /**
     * Retrieves a value from the session
     *
     * @param string                                               $key
     * @param PromiseInterface $promise
     *
     * @return SessionInterface
     */
    public function get(string $key, PromiseInterface $promise): SessionInterface;

    /**
     * Removes an item from the session
     *
     * @param string $key
     *
     * @return SessionInterface
     */
    public function remove(string $key): SessionInterface;

    /**
     * Clears the contents of the session
     *
     * @return SessionInterface
     */
    public function clear(): SessionInterface;
}
