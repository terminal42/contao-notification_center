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
 * @copyright  terminal42 gmbh 2013
 * @license    LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['title']                       = array('Title', 'Please enter a title for this gateway.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']                        = array('Type', 'Please select a type for this gateway.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['email_overrideSmtp']          = array('Override SMTP settings', 'This gateway will take the Contao e-mail settings by default. If you want to override the SMTP settings for this specific gateway, activate this checkbox.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type']                   = array('File type', 'Please choose the file type.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection']             = array('Connection type', 'Please choose the connection type.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_host']                   = array('Host name', 'Please enter the host name.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_port']                   = array('Port number', 'Here you can enter the port number. Leave empty to use the default.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_username']               = array('Username', 'Please enter the username.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_password']               = array('Password', 'Please enter the password.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_path']                   = array('Path', 'Here you can enter the path (e.g. <em>downloads</em>).');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['email']               = 'Standard email gateway';
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['file']                = 'Write to file';
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type']['csv']            = 'CSV';
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type']['xml']            = 'Plain Text / XML';
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection']['local']    = 'Local';
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection']['ftp']      = 'FTP';

/**
 * Messages
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_confirm']       = 'Connection successful';
$GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_class']   = 'Could not find FTP class!';
$GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_connect'] = 'Failed to connect to server: %s';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['new']                     = array('New gateway', 'Create a new gateway.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['edit']                    = array('Edit gateway', 'Edit gateway ID %s.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['copy']                    = array('Copy gateway', 'Copy gateway ID %s.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['delete']                  = array('Delete gateway', 'Delete gateway ID %s.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['show']                    = array('Gateway details', 'Show details for gateway ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['title_legend']            = 'Title & type';
$GLOBALS['TL_LANG']['tl_nc_gateway']['gateway_legend']          = 'Gateway settings';