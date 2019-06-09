<?php

declare(strict_types=1);

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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Foundation\Manager;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\Recipe\Chef;

/**
 * Class Manager to process requests in East Foundation. The manager
 * passes the request to each middleware as the spread has not been stopped.
 *
 * All public method of the manager must only return the self client or a clone instance.
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Manager extends Chef implements ManagerInterface
{
    /**
     * No states defined for this daughter, use directly states defined for the Chef Stated class.
     * @inheritDoc
     */
    protected static function statesListDeclaration(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function receiveRequest(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface {
        $this->process([
            'request' => $request,
            'client' => $client
        ]);

        return $this;
    }

    public function continueExecution(
        ClientInterface $client,
        ServerRequestInterface $request
    ): ManagerInterface {
        $this->continue([
            'request' => $request,
            'client' => $client
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): ManagerInterface
    {
        $this->finish(null);

        return $this;
    }
}
