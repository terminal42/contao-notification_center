<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(MailerGateway::class)
        ->args([service_locator([
            'mailer' => service('mailer'),
            'contao.string.simple_token_parser' => service('contao.string.simple_token_parser'),
            'contao.insert_tag.parser' => service('contao.insert_tag.parser'),
            'contao.framework' => service('contao.framework'),
            'contao.files' => service('contao.filesystem.virtual.files'),
        ])])
    ;
};
