<?php

namespace Teknoo\East\Framework;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Teknoo\East\Framework\DependencyInjection\EastFrameworkCompilerPass;

class EastFrameworkBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EastFrameworkCompilerPass());
    }
}