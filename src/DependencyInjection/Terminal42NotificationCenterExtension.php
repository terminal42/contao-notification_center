<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\DependencyInjection;

use Contao\CoreBundle\DependencyInjection\Filesystem\ConfigureFilesystemInterface;
use Contao\CoreBundle\DependencyInjection\Filesystem\FilesystemConfiguration;
use Contao\NewsletterBundle\ContaoNewsletterBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Gateway\GatewayInterface;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

class Terminal42NotificationCenterExtension extends Extension implements ConfigureFilesystemInterface
{
    public const BULKY_ITEMS_VFS_NAME = 'notification_center_bulky_items_storage';

    public const BULKY_ITEMS_VFS_TABLE_NAME = 'tl_nc_bulky_items';

    public const GATEWAY_TAG = 'notification_center.gateway';

    public const TYPE_TAG = 'notification_center.notification_type';

    public const TOKEN_DEFINITION_FACTORY_TAG = 'notification_center.token_definition_factory';

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

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

        $container->findDefinition(BulkyItemStorage::class)
            ->setArgument(1, $config['bulky_items_storage']['retention_period'])
        ;
    }

    public function configureFilesystem(FilesystemConfiguration $config): void
    {
        $config
            ->mountLocalAdapter('var/nc_bulky_items', 'notification_center/bulky_items')
            ->addVirtualFilesystem(self::BULKY_ITEMS_VFS_NAME, 'notification_center/bulky_items')
        ;

        $config->addDefaultDbafs(self::BULKY_ITEMS_VFS_NAME, self::BULKY_ITEMS_VFS_TABLE_NAME, 'md5', false);
    }
}
