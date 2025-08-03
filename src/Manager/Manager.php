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

namespace Teknoo\East\Foundation\Manager;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\Recipe\Chef;

/**
 * Class Manager to process requests in East Foundation. The manager
 * passes the request to each middleware as the spread has not been stopped.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Manager extends Chef implements ManagerInterface
{
    public function receiveRequest(
        ClientInterface $client,
        MessageInterface $message
    ): ManagerInterface {
        $this->process([
            MessageInterface::class => $message,
            ClientInterface::class => $client,
        ]);

        return $this;
    }

    public function continueExecution(
        ClientInterface $client,
        MessageInterface $message
    ): ManagerInterface {
        $this->continue([
            MessageInterface::class => $message,
            ClientInterface::class => $client,
        ]);

        return $this;
    }

    public function updateMessage(
        MessageInterface $message
    ): ManagerInterface {
        $this->updateWorkPlan([
            MessageInterface::class => $message,
        ]);

        return $this;
    }

    public function stop(): ManagerInterface
    {
        $this->finish(null);

        return $this;
    }
}
