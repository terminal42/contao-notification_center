<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\ChainTokenDefinitionFactory;

class TokenDefinitionFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ChainTokenDefinitionFactory::class)) {
            return;
        }

        $definition = $container->findDefinition(ChainTokenDefinitionFactory::class);

        $taggedServices = $container->findTaggedServiceIds(Terminal42NotificationCenterExtension::TOKEN_DEFINITION_FACTORY_TAG);

        foreach (array_keys($taggedServices) as $id) {
            // Do not add chain itself which is the whole point of this compiler pass. We can
            // use tagged_iterator() in combination with some exclude attributes as soon was
            // we can require symfony/dependency-injection >= 6.1
            if (ChainTokenDefinitionFactory::class === $id) {
                continue;
            }

            $definition->addMethodCall('addFactory', [new Reference($id)]);
        }
    }
}
