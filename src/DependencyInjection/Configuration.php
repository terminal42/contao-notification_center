<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('terminal42_notification_center');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('bulky_items_storage')
            ->addDefaultsIfNotSet()
            ->children()
            ->integerNode('retention_period')
            ->info('The number of days for which bulky items are kept in storage.')
            ->min(1)
            ->defaultValue(7)
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
