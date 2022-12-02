<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection;

use Contao\NewsletterBundle\ContaoNewsletterBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Terminal42\NotificationCenterBundle\Gateway\GatewayInterface;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

class Terminal42NotificationCenterExtension extends Extension
{
    public const GATEWAY_TAG = 'notification_center.gateway';
    public const TYPE_TAG = 'notification_center.notification_type';
    public const TOKEN_DEFINITION_FACTORY_TAG = 'notification_center.token_definition_factory';

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');
        $loader->load('gateways.php');
        $loader->load('listeners.php');
        $loader->load('notification_types.php');
        $loader->load('modules.php');

        $container->registerForAutoconfiguration(GatewayInterface::class)
            ->addTag(self::GATEWAY_TAG)
        ;
        $container->registerForAutoconfiguration(NotificationTypeInterface::class)
            ->addTag(self::TYPE_TAG)
        ;
        $container->registerForAutoconfiguration(TokenDefinitionFactoryInterface::class)
            ->addTag(self::TOKEN_DEFINITION_FACTORY_TAG)
        ;

        if (ContainerBuilder::willBeAvailable('contao/news-bundle', ContaoNewsletterBundle::class, ['terminal42/notification_center'])) {
            $loader->load('newsletter_bundle.php');
        }
    }
}
