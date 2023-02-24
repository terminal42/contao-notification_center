<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Codefog\HasteBundle\Formatter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\Backend\AutoSuggester;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\EventListener\AdminEmailTokenSubscriber;
use Terminal42\NotificationCenterBundle\EventListener\Backend\BackendMenuSubscriber;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\FormListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\GatewayListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\LanguageListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\MessageListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\ModuleListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\NotificationListener;
use Terminal42\NotificationCenterBundle\EventListener\DbafsMetadataSubscriber;
use Terminal42\NotificationCenterBundle\EventListener\DisableDeliverySubscriber;
use Terminal42\NotificationCenterBundle\EventListener\DoctrineSchemaListener;
use Terminal42\NotificationCenterBundle\EventListener\NotificationTypeForModuleConfigSubscriber;
use Terminal42\NotificationCenterBundle\EventListener\ProcessFormDataListener;
use Terminal42\NotificationCenterBundle\EventListener\RegistrationListener;
use Terminal42\NotificationCenterBundle\EventListener\UpdatePersonalDataListener;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(FormListener::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;

    $services->set(GatewayListener::class)
        ->args([
            service(GatewayRegistry::class),
            service('contao.mailer.available_transports'),
        ])
    ;

    $services->set(LanguageListener::class)
        ->args([
            service(AutoSuggester::class),
            service('database_connection'),
            service(ConfigLoader::class),
            service('contao.intl.locales'),
            service(TranslatorInterface::class),
            service('security.helper'),
        ])
    ;

    $services->set(MessageListener::class)
        ->args([
            service(AutoSuggester::class),
            service(ConfigLoader::class),
            service('contao.framework'),
            service('database_connection'),
            service('contao.intl.locales'),
            service('security.helper'),
            service('twig'),
        ])
    ;

    $services->set(ModuleListener::class)
        ->args([
            service(ConfigLoader::class),
            service('event_dispatcher'),
            service(NotificationCenter::class),
        ])
    ;

    $services->set(NotificationListener::class)
        ->args([
            service(NotificationTypeRegistry::class),
        ])
    ;

    $services->set(BackendMenuSubscriber::class)
        ->args([
            service('assets.packages'),
        ])
    ;

    $services->set(AdminEmailTokenSubscriber::class)
        ->args([
            service('request_stack'),
            service(TokenDefinitionFactoryInterface::class),
            service('contao.framework'),
        ])
    ;

    $services->set(DisableDeliverySubscriber::class)
        ->args([
            service('contao.string.simple_token_parser'),
            service('contao.string.simple_token_expression_language'),
        ])
    ;

    $services->set(NotificationTypeForModuleConfigSubscriber::class);

    $services->set(ProcessFormDataListener::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;

    $services->set(RegistrationListener::class)
        ->args([
            service(NotificationCenter::class),
            service('request_stack'),
            service(Formatter::class),
        ])
    ;

    $services->set(UpdatePersonalDataListener::class)
        ->args([
            service(NotificationCenter::class),
            service('request_stack'),
            service(Formatter::class),
            service('contao.routing.scope_matcher'),
            service('security.helper'),
            service('twig'),
        ])
    ;

    $services->set(DoctrineSchemaListener::class)
        ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema'])
    ;

    $services->set(DbafsMetadataSubscriber::class);
};
