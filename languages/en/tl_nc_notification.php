<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2014
 * @license    LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['title']                   = array('Title', 'Please enter a title for this notification.');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']                    = array('Type', 'Please select a type for this notification.');

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

/**
 * Notification types
 */
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['core']            = 'Core';
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['core_form']       = array('Form submission', 'This notification type can be sent when the form is submitted.');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['contao']              = 'Contao';
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_registration'] = array('Member registration');
$GLOBALS['TL_LANG']['tl_nc_notification']['type']['member_password']     = array('Member lost password');
