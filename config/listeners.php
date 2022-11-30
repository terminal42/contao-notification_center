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
use Terminal42\NotificationCenterBundle\EventListener\DisableDeliverySubscriber;
use Terminal42\NotificationCenterBundle\EventListener\MessageTypeForModuleConfigSubscriber;
use Terminal42\NotificationCenterBundle\EventListener\ProcessFormDataListener;
use Terminal42\NotificationCenterBundle\EventListener\RegistrationListener;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;
use Terminal42\NotificationCenterBundle\NotificationCenter;

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
        ])
    ;

    $services->set(MessageListener::class)
        ->args([
            service(AutoSuggester::class),
            service(ConfigLoader::class),
            service('contao.framework'),
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
            service(MessageTypeRegistry::class),
        ])
    ;

    $services->set(BackendMenuSubscriber::class);

    $services->set(AdminEmailTokenSubscriber::class)
        ->args([
            service('request_stack'),
        ])
    ;

    $services->set(DisableDeliverySubscriber::class)
        ->args([
            service('contao.string.simple_token_parser'),
            service('contao.string.simple_token_expression_language'),
        ])
    ;

    $services->set(MessageTypeForModuleConfigSubscriber::class);

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
};