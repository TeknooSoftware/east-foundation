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

namespace Teknoo\East\Foundation\Processor;

use Psr\Http\Message\MessageInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Router\ResultInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

use function array_diff_key;
use function array_filter;
use function array_flip;
use function is_array;
use function is_string;
use function str_contains;

use const ARRAY_FILTER_USE_KEY;

/**
 * Processor implementation to inject the controller returned by the router into the dedicated place in the workplan
 * to allow the chef to execute it via a DynamicBowl.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Processor implements ProcessorInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * @param string[] $protectedParameters
     */
    public function __construct(
        private readonly bool $clientInSilentMode = false,
        private readonly array $protectedParameters = [
            'formOptions',
            'errorCode',
            'errorMessage',
            'workPlanKey',
            'constructorArguments',
        ],
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
     * @param array<mixed, mixed>|object|null $parameters
     * @return array<string, mixed>
     */
    private function filterParameters(array|null|object $parameters): array
    {
        if (!is_array($parameters)) {
            return [];
        }

        $parameters = array_diff_key($parameters, array_flip($this->protectedParameters));

        $final = [];
        foreach ($parameters as $name => &$value) {
            if (is_string($name) && !str_contains($name, '\\')) {
                $final[$name] = $value;
            }
        }

        return $final;
    }

    /**
     * @return array<string, mixed>
     */
    private function getParameters(ServerRequestInterface $request): array
    {
        $values = $request->getAttributes()
            + $this->filterParameters($request->getParsedBody())
            + $this->filterParameters($request->getQueryParams());

        return array_filter(
            array: $values,
            callback: static fn (mixed $key): bool => is_string($key),
            mode: ARRAY_FILTER_USE_KEY
        );
    }
}
