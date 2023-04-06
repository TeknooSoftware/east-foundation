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

namespace Teknoo\East\Foundation\Middleware;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * Interface to define middleware, able to manage a request, following the #East philosophy : It's the middleware who
 * call the manager to call the next middleware, the manager not manage any return from middleware.
 *
 * Router are specific middleware.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface MiddlewareInterface
{
    /*
     * Method called by a manager to ask the middleware if it can process the request.
     */
    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager
    ): MiddlewareInterface;
}
