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

namespace Teknoo\East\Foundation\Processor;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Processor implementation to inject the controller returned by the router into the dedicated place in the workplan
 * to allow the chef to execute it via a DynamicBowl.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Processor implements ProcessorInterface, ImmutableInterface
{
    use ImmutableTrait;

    public function __construct(
        private readonly bool $clientInSilentMode = false,
    ) {
    }

    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager,
        ?ResultInterface $result = null
    ): MiddlewareInterface {
        $client->sendAResponseIsOptional();

        if (!$result instanceof ResultInterface) {
            return $this;
        }

        $parameters = [];
        $mandatory = [
            ClientInterface::class => $client,
            MessageInterface::class => $message,
            ManagerInterface::class => $manager
        ];

        if ($message instanceof ServerRequestInterface) {
            $parameters = $this->getParameters($message);
            $mandatory['request'] = $message;
        }

        $values = $mandatory + [self::WORK_PLAN_CONTROLLER_KEY => $result->getController()] + $parameters;

        if (false === $this->clientInSilentMode) {
            $client->mustSendAResponse();
        }

        $manager->updateWorkPlan($values);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function getParameters(ServerRequestInterface $request): array
    {
        return (array) $request->getAttributes()
            + (array) $request->getParsedBody()
            + (array) $request->getQueryParams();
    }
}
