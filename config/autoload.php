<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Register PSR-0 namespace
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('NotificationCenter', 'system/modules/notification_center/library');
}


/**
 * Register classes outside the namespace folder
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::addClassMap(array
    (
        // DCA Helpers
        'NotificationCenter\tl_form'                => 'system/modules/notification_center/classes/tl_form.php',
        'NotificationCenter\tl_member'              => 'system/modules/notification_center/classes/tl_member.php',
        'NotificationCenter\tl_module'              => 'system/modules/notification_center/classes/tl_module.php',
        'NotificationCenter\tl_nc_gateway'          => 'system/modules/notification_center/classes/tl_nc_gateway.php',
        'NotificationCenter\tl_nc_notification'     => 'system/modules/notification_center/classes/tl_nc_notification.php',
        'NotificationCenter\tl_nc_language'         => 'system/modules/notification_center/classes/tl_nc_language.php',
        'NotificationCenter\tl_nc_message'          => 'system/modules/notification_center/classes/tl_nc_message.php',
        'NotificationCenter\tl_nc_queue'            => 'system/modules/notification_center/classes/tl_nc_queue.php'
    ));
}


/**
 * Register Contao classes
 */
ClassLoader::addClasses(array
(
	'Contao\ModulePasswordNotificationCenter' => 'system/modules/notification_center/modules/ModulePasswordNotificationCenter.php',
));
