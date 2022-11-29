<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\Controller\FrontendModule\LostPasswordController;
use Terminal42\NotificationCenterBundle\NotificationCenter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(LostPasswordController::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;
};
