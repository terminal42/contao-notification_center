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
$GLOBALS['TL_LANG']['tl_nc_gateway']['title']                       = array('Title', 'Please enter a title for this gateway.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']                        = array('Type', 'Please select a type for this gateway.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_targetGateway']         = array('Target gateway', 'This gateway will queue all the messages and then send them over the gateway you define here.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronEnable']            = array('Enable poor man\'s cronjob', 'This will register this queue gateway to the poor man\'s cronjob.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']          = array('Interval', 'Choose the interval you would like to have this queue gateway be invoked.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronMessages']          = array('Number of messages', 'Here you can enter the number of messages that should be sent per invocation.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['email_overrideSmtp']          = array('Override SMTP settings', 'This gateway will take the Contao e-mail settings by default. If you want to override the SMTP settings for this specific gateway, activate this checkbox.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type']                   = array('File type', 'Please choose the file type.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection']             = array('Connection type', 'Please choose the connection type.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_host']                   = array('Host name', 'Please enter the host name.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_port']                   = array('Port number', 'Here you can enter the port number. Leave empty to use the default.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_username']               = array('Username', 'Please enter the username.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_password']               = array('Password', 'Please enter the password.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['file_path']                   = array('Path', 'Here you can enter the path (e.g. <em>downloads</em>).');
$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_key']                = array('Postmark API key', 'Please enter your unique Postmark API key.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_test']               = array('Enable test mode', 'Here you can enable the test mode.');
$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_ssl']                = array('Enable SSL', 'Here you can enable the SSL connection.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['queue']                   = 'Queue';
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['email']                   = 'Standard email gateway';
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['file']                    = 'Write to file';
$GLOBALS['TL_LANG']['tl_nc_gateway']['type']['postmark']                = 'Postmark (postmarkapp.com)';
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']['minutely']  = 'Every minute';
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']['hourly']    = 'Every hour';
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']['daily']     = 'Every day';
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']['weekly']    = 'Every week';
$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval']['monthly']   = 'Every month';
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
$GLOBALS['TL_LANG']['tl_nc_gateway']['cronjob_legend']          = 'Cronjob settings';

/**
 * Others
 */
$GLOBALS['TL_LANG']['queueCronjobExplanation'] = 'Queued messages will remain in the queue forever unless
you trigger the sending mechanism by either using a real cron job or
the Contao internal poor man\'s cronjob. The Notification Center is shipped
with a binary that can be executed using a real cronjob. To setup a real cronjob
that invokes the queue of this queue gateway (ID: {gateway_id}) and send 15 messages every 10 minutes,
you would need to setup the following crontab:
<br><blockquote>*/10 * * * * /path/to/contao/system/modules/notification_center/bin/queue -s {gateway_id} -n 15</blockquote><br>
or let\'s say you want to send 30 messages every 5 minutes after every hour, then you would set it up like this:
<br><blockquote>5 * * * * /path/to/contao/system/modules/notification_center/bin/queue -s {gateway_id} -n 30</blockquote><br>
If you don\'t have access to real cronjobs then you can enable the poor man\'s cron. Note that it doesn\'t provide the same
flexibility in terms of interval settings and it is subject to the web execution context and thus certainly affected by
PHP configurations such as the maximum execution time. Thus, try to keep the number of messages sent per invocation as low as possible.';
