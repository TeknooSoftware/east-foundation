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

namespace Teknoo\East\FoundationBundle\Extension;

use Symfony\Component\Routing\Loader\Configurator\ImportConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * Extension module, called in the kernel of your symfony app to extend, add path to import into
 * the routing configurator to embed some route in your extension without references them in your symfony configuration
 * This module works in class context with a static method, because DI is not already available at this execution step
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @phpstan-consistent-constructor
 */
final class Routes implements ModuleInterface
{
    private function __construct(
        private readonly RoutingConfigurator $routes,
        private readonly string $environment,
    ) {
    }

    /**
     * @param string|array<string, mixed> $resource
     * @param string|string[]|null $exclude Glob patterns to exclude from the import
     */
    public function import(
        string|array $resource,
        ?string $type = null,
        bool $ignoreErrors = false,
        string|array|null $exclude = null,
    ): ImportConfigurator {
        return $this->routes->import($resource, $type, $ignoreErrors, $exclude);
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public static function extendsRoutes(
        RoutingConfigurator $routes,
        string $environment,
        ?ManagerInterface $manager = null
    ): void {
        $module = new static($routes, $environment);

        ($manager ?? Manager::run())->execute($module);
    }
}
