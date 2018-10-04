<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['title']                   = array('Title', 'Please enter a title for this notification.');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']                    = array('Type', 'Please select a type for this notification.');
$GLOBALS['TL_LANG']['tl_nc_notification']['flatten_delimiter']       = array('Delimiter for lists', 'When list values (array values) are submitted, the Notification Center flattens them automatically by using a comma (",") by default. Change this here if you like.');
$GLOBALS['TL_LANG']['tl_nc_notification']['templates']               = array('Token Templates', 'Choose templates to generate tokens from their output. Each template name must start with notification_xxx and its token will be converted to a ##template_xxx## token.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['email']           = 'Standard eMail gateway';
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['file']            = 'Write to file';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['new']                     = array('New notification', 'Create a new notification.');
$GLOBALS['TL_LANG']['tl_nc_notification']['edit']                    = array('Manage notifications', 'Manage messages for notification ID %s.');
$GLOBALS['TL_LANG']['tl_nc_notification']['editheader']              = array('Edit notification', 'Edit notification ID %s.');
$GLOBALS['TL_LANG']['tl_nc_notification']['copy']                    = array('Copy notification', 'Copy notification ID %s.');
$GLOBALS['TL_LANG']['tl_nc_notification']['delete']                  = array('Delete notification', 'Delete notification ID %s.');
$GLOBALS['TL_LANG']['tl_nc_notification']['show']                    = array('Notification details', 'Show details for notification ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['title_legend']            = 'Title & type';
$GLOBALS['TL_LANG']['tl_nc_notification']['config_legend']           = 'Configuration';
$GLOBALS['TL_LANG']['tl_nc_notification']['templates_legend']        = 'Templates';

/**
 * Notification types
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['contao']                 = 'Contao';
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['core_form']              = array('Form submission', 'This notification type can be sent when the form is submitted.');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_activation']      = array('Member activation');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_registration']    = array('Member registration');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_personaldata']    = array('Member personal data');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_password']        = array('Member lost password');
