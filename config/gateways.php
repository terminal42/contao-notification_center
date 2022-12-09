<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(MailerGateway::class)
        ->args([
            service('contao.framework'),
            service('contao.filesystem.virtual.files'),
            service('mailer'),
        ])
    ;
};
