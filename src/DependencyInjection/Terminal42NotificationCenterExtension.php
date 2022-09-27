<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Terminal42\NotificationCenterBundle\Gateway\GatewayInterface;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeInterface;

class Terminal42NotificationCenterExtension extends Extension
{
    public const GATEWAY_TAG = 'notification_center.gateway';
    public const TYPE_TAG = 'notification_center.type';

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(GatewayInterface::class)
            ->addTag(self::GATEWAY_TAG)
        ;
        $container->registerForAutoconfiguration(MessageTypeInterface::class)
            ->addTag(self::TYPE_TAG)
        ;
    }
}
