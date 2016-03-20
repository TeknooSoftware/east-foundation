<?php

namespace Teknoo\East\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class BlockCompilerPass
 * To link all services able to provide content for blocks
 * @package BoxOffice\FilmBundle\DependencyInjection
 */
class EastFrameworkCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $taggedControllerService = $container->findTaggedServiceIds('east.controller.service');

        foreach ($taggedControllerService as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall(
                'setContainer',
                [new Reference('service_container')]
            );
        }
    }
}