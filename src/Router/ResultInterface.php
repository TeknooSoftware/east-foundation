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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Router;

use Teknoo\Immutable\ImmutableInterface;

/**
 * Interface `ResultInterface` to represent immutable object implementing a result of a router. A router can return
 * several results for a same request. Next results are available from the method `getNext`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ResultInterface extends ImmutableInterface
{
    /*
     * To know the controller to call for this route.
     */
    public function getController(): callable;

    /*
     * If there are several result found by the router for a same request, next router result can be fetched by this
     * method. Else this method must return null.
     */
    public function getNext(): ?ResultInterface;
}
