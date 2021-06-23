<?php

/**
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
            'request' => $message, //@deprecated
            'client' => $client, //@deprecated
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
            'request' => $message, //@deprecated
            'client' => $client, //@deprecated
        ]);

        return $this;
    }

    public function updateMessage(
        MessageInterface $message
    ): ManagerInterface {
        $this->updateWorkPlan([
            MessageInterface::class => $message,
            'request' => $message, //@deprecated
        ]);

        return $this;
    }

    public function stop(): ManagerInterface
    {
        $this->finish(null);

        return $this;
    }
}
