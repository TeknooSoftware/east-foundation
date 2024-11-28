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

namespace Teknoo\East\Foundation\Extension;

/**
 * Contract to define a manager extension. The manager class must be able to return ots singleton when
 * its static method 'run' is called.
 * A singleton is provided because when extension are used because containers DI are often not available.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ManagerInterface
{
    public function __construct(?LoaderInterface $loader = null);

    /**
     * Return a manager singleton because when extension are used because containers DI are often not available.
     */
    public static function run(?LoaderInterface $loader = null): ManagerInterface;

    /**
     * Method called by modules to be forwarded to extensions to allow them to extend the application's capacity
     * like reference new bundles, update the container di, add some routes, etc...
     */
    public function execute(ModuleInterface $module): ManagerInterface;

    /**
     * @return iterable<class-string<ExtensionInterface>, string>
     */
    public function listLoadedExtensions(): iterable;
}
