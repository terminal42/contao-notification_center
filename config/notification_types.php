<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\LostPasswordNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\MemberActivationNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\MemberPersonalDataNotificationType;
use Terminal42\NotificationCenterBundle\NotificationType\MemberRegistrationNotificationType;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(FormGeneratorNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(LostPasswordNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(MemberActivationNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(MemberRegistrationNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(MemberPersonalDataNotificationType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
};
