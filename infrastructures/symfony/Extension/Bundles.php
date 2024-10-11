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
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\Extension;

use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * Extension module to extend Symfony bundle list declaration
 *
 * This module works in class context with a static method, because DI is not already available at this execution step
 * This module passes to the extension manager an instance of this class to allow extension to add bundles to the list
 * without manipulate the bundles's array.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @phpstan-consistent-constructor
 */
class Bundles implements ModuleInterface
{
    /**
     * @param array<string, array<string, boolean>> $bundles
     */
    private function __construct(
        private array $bundles
    ) {
    }

    /**
     * @param array<string, boolean> $environments
     */
    public function register(string $bundleClass, array $environments): self
    {
        $this->bundles[$bundleClass] = $environments;

        return $this;
    }

    /**
     * @param array<string, array<string, boolean>> $bundles
     */
    public static function extendsBundles(array &$bundles, ?ManagerInterface $manager = null): void
    {
        $module = new static($bundles);

        ($manager ?? Manager::run())->execute($module);

        $bundles = $module->bundles;
    }
}
