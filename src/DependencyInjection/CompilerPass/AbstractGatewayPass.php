<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;
use Terminal42\NotificationCenterBundle\Gateway\AbstractGateway;

class AbstractGatewayPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(Terminal42NotificationCenterExtension::GATEWAY_TAG);
        $locateableServices = [
            AbstractGateway::SERVICE_NAME_SIMPLE_TOKEN_PARSER => new Reference('contao.string.simple_token_parser', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            AbstractGateway::SERVICE_NAME_INSERT_TAG_PARSER => new Reference('contao.insert_tag.parser', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ];

        foreach (array_keys($taggedServices) as $id) {
            $definition = $container->findDefinition($id);

            if (!is_a($definition->getClass(), AbstractGateway::class, true)) {
                continue;
            }

            $definition->addMethodCall('setContainer', [ServiceLocatorTagPass::register($container, $locateableServices)]);
        }
    }
}
