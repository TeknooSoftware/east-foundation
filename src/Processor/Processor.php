<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Foundation\Processor;

use Teknoo\East\Foundation\Http\ClientInterface;
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Processor implements ProcessorInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager,
        ResultInterface $result = null
    ): MiddlewareInterface {
        if (!$result instanceof ResultInterface) {
            return $this;
        }

        $values = \array_merge(
            $this->getParameters($request),
            [self::WORK_PLAN_CONTROLLER_KEY => $result->getController()],
            //To prevent overloading from request.
            ['client' => $client, 'request' => $request, 'manager' => $manager]
        );

        $manager->updateWorkPlan($values);

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array<string, mixed>
     */
    private function getParameters(ServerRequestInterface $request): array
    {
        return \array_merge(
            (array) $request->getQueryParams(),
            (array) $request->getParsedBody(),
            (array) $request->getAttributes()
        );
    }
}
