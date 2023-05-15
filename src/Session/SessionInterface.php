<?php

/*
 * East Foundation.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Session;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define object representing sessions in the request to allow developpers to share data between requests.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

    /**
     * Retrieves a value from the session
     *
     * @param PromiseInterface<mixed, mixed> $promise
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
