<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Controller\DownloadBulkyItemController;

return static function (RoutingConfigurator $routes): void {
    $routes->add('nc_bulky_item_download', '/notifications/download/{voucher}')
        ->controller(DownloadBulkyItemController::class)
        ->requirements(['voucher' => BulkyItemStorage::VOUCHER_REGEX])
    ;
};
