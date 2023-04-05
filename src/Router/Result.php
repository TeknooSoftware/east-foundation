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

use Teknoo\Immutable\ImmutableTrait;

/**
 * Immutable object implementing a result of a router. A router can return
 * several results for a same request. Next results are available from the method `getNext`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Result implements ResultInterface
{
    use ImmutableTrait;

    /**
     * @var callable
     */
    private $controller;

    public function __construct(
        callable $controller,
        private readonly ?ResultInterface $next = null
    ) {
        $this->uniqueConstructorCheck();

        $this->controller = $controller;
    }

    public function getController(): callable
    {
        return $this->controller;
    }

    public function getNext(): ?ResultInterface
    {
        return $this->next;
    }
}
