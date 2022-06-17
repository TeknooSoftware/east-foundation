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

namespace Teknoo\East\FoundationBundle\Messenger;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;

/**
 * Class to use with Symfony Message's handler to execute a message in a East application via the manager.
 * The workplan to pass is the responsibility of the handler.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Executor
{
    public function __construct(
        private readonly ManagerInterface $manager,
    ) {
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    public function execute(
        BaseRecipeInterface $recipe,
        MessageInterface $message,
        ClientInterface $client,
        array $workPlan,
    ): self {
        $manager = clone $this->manager;

        $manager->read($recipe);

        $workPlan[MessageInterface::class] = $message;
        $workPlan[ClientInterface::class] = $client;

        $manager->process($workPlan);

        return $this;
    }
}
