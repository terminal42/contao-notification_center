<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\Backend\AutoSuggester;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Cron\PruneBulkyItemStorageCron;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\ChainTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\CoreTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Util\FileUploadNormalizer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(AutoSuggester::class)
        ->args([
            service('assets.packages'),
            service(NotificationCenter::class),
            service(TranslatorInterface::class),
        ])
    ;

    $services->set(GatewayRegistry::class)
        ->args([
            tagged_iterator(Terminal42NotificationCenterExtension::GATEWAY_TAG),
        ])
    ;

    $services->set(NotificationTypeRegistry::class)
        ->args([
            tagged_iterator(Terminal42NotificationCenterExtension::TYPE_TAG),
        ])
    ;

    $services->set(ChainTokenDefinitionFactory::class);
    $services->set(CoreTokenDefinitionFactory::class);
    $services->alias(TokenDefinitionFactoryInterface::class, ChainTokenDefinitionFactory::class);

    $services->set(ConfigLoader::class)
        ->args([
            service('database_connection'),
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.reset', 'method' => 'reset'])
    ;

    $services->set(BulkyItemStorage::class)
        ->args([
            service('contao.filesystem.virtual.'.Terminal42NotificationCenterExtension::BULKY_ITEMS_VFS_NAME),
        ])
    ;

    $services->set(PruneBulkyItemStorageCron::class)
        ->args([
            service(BulkyItemStorage::class),
        ])
    ;

    $services->set(NotificationCenter::class)
        ->args([
            service('database_connection'),
            service(NotificationTypeRegistry::class),
            service(GatewayRegistry::class),
            service(ConfigLoader::class),
            service('event_dispatcher'),
            service('request_stack'),
            service(BulkyItemStorage::class),
        ])
    ;

    $services->set(FileUploadNormalizer::class)
        ->args([
            param('kernel.project_dir'),
            service('mime_types'),
            service('contao.filesystem.virtual.files'),
        ])
    ;
};
