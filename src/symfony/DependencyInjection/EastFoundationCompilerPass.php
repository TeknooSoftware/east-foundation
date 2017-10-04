<?php
/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\FoundationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EastFoundationCompilerPass
 * Compiler pass to inject service container to east framework controller.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class EastFoundationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): EastFoundationCompilerPass
    {
        $taggedControllers = $container->findTaggedServiceIds('east.controller.service');

        $routerPresent = $container->has('router');
        $twigPresent = $container->has('twig');
        $tokenStoragePresent = $container->has('security.token_storage');

        foreach ($taggedControllers as $id => $tags) {
            $definition = $container->getDefinition($id);

            if (!empty($routerPresent)) {
                $definition->addMethodCall('setRouter', [new Reference('router')]);
            }

            if (!empty($twigPresent)) {
                $definition->addMethodCall('setTwig', [new Reference('twig')]);
            }

            if (!empty($tokenStoragePresent)) {
                $definition->addMethodCall('setTokenStorage', [new Reference('security.token_storage')]);
            }
        }

        return $this;
    }
}
