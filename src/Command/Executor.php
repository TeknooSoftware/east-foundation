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
 * @link        https://teknoo.software/east-collection/foundation Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
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
