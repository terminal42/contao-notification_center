<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\NotificationCenterBundle\DependencyInjection\CompilerPass\TokenDefinitionFactoryPass;

class Terminal42NotificationCenterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TokenDefinitionFactoryPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
