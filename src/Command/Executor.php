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

namespace Teknoo\East\Foundation\Command;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Recipe;

/**
 * Class to use in CLI context to execute a message in a East application via the manager.
 * The workplan to pass is the responsibility of the handler.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

        if ($recipe instanceof Recipe) {
            $recipe = $recipe->onError($client->errorInRequest(...));
        }

        $manager->read($recipe);

        $workPlan[MessageInterface::class] = $message;
        $workPlan[ClientInterface::class] = $client;

        $manager->process($workPlan);

        return $this;
    }
}
