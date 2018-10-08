<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['notification_center']       = 'Notification Center';
$GLOBALS['TL_LANG']['MOD']['nc_notifications']          = array('Notifications', 'Manage notifications.');
$GLOBALS['TL_LANG']['MOD']['nc_queue']                  = array('Queue', 'View the message queue.');
$GLOBALS['TL_LANG']['MOD']['nc_gateways']               = array('Gateways', 'Manage gateways');

/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['lostPasswordNotificationCenter']          = array('Lost password (Notification Center)', 'Generates a form to request a new password and sends the notification using the notification center.');
$GLOBALS['TL_LANG']['FMD']['newsletterSubscribeNotificationCenter']   = array('Subscribe (Notification Center)', 'Generates a form to subscribe to one or more channels and sends the notification using the notification center.');
$GLOBALS['TL_LANG']['FMD']['newsletterActivateNotificationCenter']    = array('Activate(Notification Center)', 'Generates a form to activate subscription to one or more channels the notification using the notification center.');
$GLOBALS['TL_LANG']['FMD']['newsletterUnsubscribeNotificationCenter'] = array('Unsubscribe (Notification Center)', 'Generates a form to unsubscribe from one or more channels and sends the notification using the notification center.');
