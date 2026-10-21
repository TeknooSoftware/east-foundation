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

namespace Teknoo\East\FoundationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Teknoo\East\FoundationBundle\Router\AccessCheckerInterface;
use Teknoo\East\FoundationBundle\Router\AccessControlChecker;
use Teknoo\East\Twig\Template\Engine;

/**
 * Class EastFoundationCompilerPass
 * Compiler pass to inject service container to east framework controller, and to remove the access checker of the
 * router, for Live Component requests, when the Symfony firewall (and its access listener) is not available.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EastFoundationCompilerPass implements CompilerPassInterface
{
    private function removeAccessCheckerWithoutFirewall(ContainerBuilder $container): void
    {
        if ($container->has('security.access_listener')) {
            return;
        }

        $container->removeDefinition(AccessControlChecker::class);

        if (
            $container->hasAlias(AccessCheckerInterface::class)
            && AccessControlChecker::class === (string) $container->getAlias(AccessCheckerInterface::class)
        ) {
            $container->removeAlias(AccessCheckerInterface::class);
        }
    }

    private function injectTemplating(ContainerBuilder $container): void
    {
        $taggedControllers = $container->findTaggedServiceIds('east.endpoint.template');

        $twigPresent = $container->has('twig');

        if (false === $twigPresent) {
            return;
        }

        foreach ($taggedControllers as $id => $tags) {
            $definition = $container->getDefinition($id);

            $definition->addMethodCall(
                'setTemplating',
                [new Reference(Engine::class)]
            );
        }
    }

    public function process(ContainerBuilder $container): void
    {
        $this->removeAccessCheckerWithoutFirewall($container);
        $this->injectTemplating($container);
    }
}
