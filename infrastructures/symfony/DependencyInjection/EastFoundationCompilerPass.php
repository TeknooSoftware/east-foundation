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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east-foundation Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\FoundationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Teknoo\East\Twig\Template\Engine;

/**
 * Class EastFoundationCompilerPass
 * Compiler pass to inject service container to east framework controller.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EastFoundationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): EastFoundationCompilerPass
    {
        $taggedControllers = $container->findTaggedServiceIds('east.endpoint.template');

        $twigPresent = $container->has('twig');

        if (false === $twigPresent) {
            return $this;
        }

        foreach ($taggedControllers as $id => $tags) {
            $definition = $container->getDefinition($id);

            $definition->addMethodCall(
                'setTemplating',
                [new Reference(Engine::class)]
            );
        }

        return $this;
    }
}
