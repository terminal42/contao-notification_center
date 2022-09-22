<?php

declare(strict_types=1);

$GLOBALS['BE_MOD']['notification_center'] = [
    'nc_notifications' => [
        'tables' => ['tl_nc_notification', 'tl_nc_message', 'tl_nc_language'],
    ],
    'nc_gateways' => [
        'tables' => ['tl_nc_gateway'],
    ],
];
