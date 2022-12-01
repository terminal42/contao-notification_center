<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\Controller\FrontendModule\Newsletter\SubscribeController;
use Terminal42\NotificationCenterBundle\Controller\FrontendModule\Newsletter\UnsubscribeController;
use Terminal42\NotificationCenterBundle\EventListener\Newsletter\ActivationListener;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\Newsletter\NewsletterActivateNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\Newsletter\NewsletterSubscribeNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\Newsletter\NewsletterUnsubscribeNotificationType;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(NewsletterActivateNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;

    $services->set(NewsletterSubscribeNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;

    $services->set(NewsletterUnsubscribeNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;

    $services->set(SubscribeController::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;

    $services->set(UnsubscribeController::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;

    $services->set(ActivationListener::class)
        ->args([
            service(NotificationCenter::class),
            service('contao.framework'),
        ])
    ;
};
