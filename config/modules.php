<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Codefog\HasteBundle\Formatter;
use Codefog\HasteBundle\UrlParser;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Terminal42\NotificationCenterBundle\Controller\FrontendModule\LostPasswordController;
use Terminal42\NotificationCenterBundle\Controller\FrontendModule\RegistrationController;
use Terminal42\NotificationCenterBundle\NotificationCenter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autoconfigure();

    $services->set(LostPasswordController::class)
        ->args([
            service(ContentUrlGenerator::class),
            service(NotificationCenter::class),
            service(Formatter::class),
        ])
    ;

    $services->set(RegistrationController::class)
        ->args([
            service(NotificationCenter::class),
            service('request_stack'),
            service('contao.opt_in'),
            service(Formatter::class),
            service(UrlParser::class),
        ])
    ;
};
