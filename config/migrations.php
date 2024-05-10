<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\Migration\EmailGatewayMigration;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(EmailGatewayMigration::class)
        ->args([
            service('database_connection'),
        ]);
};
