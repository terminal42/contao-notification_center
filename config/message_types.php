<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\MessageType\FormGeneratorMessageType;
use Terminal42\NotificationCenterBundle\MessageType\LostPasswordMessageType;
use Terminal42\NotificationCenterBundle\MessageType\MemberActivationMessageType;
use Terminal42\NotificationCenterBundle\MessageType\MemberRegistrationMessageType;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(FormGeneratorMessageType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(LostPasswordMessageType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(MemberActivationMessageType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
    $services->set(MemberRegistrationMessageType::class)
        ->args([
            service(TokenDefinitionFactoryInterface::class),
        ])
    ;
};
