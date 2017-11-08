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
$GLOBALS['TL_LANG']['tl_nc_queue']['sourceQueue'][0]    = 'Source queue gateway';
$GLOBALS['TL_LANG']['tl_nc_queue']['targetGateway'][0]  = 'Target gateway';
$GLOBALS['TL_LANG']['tl_nc_queue']['message'][0]        = 'Source message';
$GLOBALS['TL_LANG']['tl_nc_queue']['dateAdded'][0]      = 'Date added to queue';
$GLOBALS['TL_LANG']['tl_nc_queue']['dateDelay'][0]      = 'Date delayed in queue';
$GLOBALS['TL_LANG']['tl_nc_queue']['dateSent'][0]       = 'Date sent from queue';
$GLOBALS['TL_LANG']['tl_nc_queue']['error'][0]          = 'Had an error during delivery process';
$GLOBALS['TL_LANG']['tl_nc_queue']['tokens'][0]         = 'Tokens';
$GLOBALS['TL_LANG']['tl_nc_queue']['language'][0]       = 'Language';

/**
 * Status
 */
$GLOBALS['TL_LANG']['tl_nc_queue']['status']['queued']                       = 'Waiting in queue.';
$GLOBALS['TL_LANG']['tl_nc_queue']['status']['error']                        = 'Error sending the message. Check the system log for more details.';
$GLOBALS['TL_LANG']['tl_nc_queue']['status']['sent']                         = 'Message sent.';
$GLOBALS['TL_LANG']['tl_nc_queue']['source']                                 = 'Source';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_queue']['re-queue']  = array('Add to queue again', 'This queued message (ID %s) encountered an error but you can re-add it to the queue by clicking this button.');
$GLOBALS['TL_LANG']['tl_nc_queue']['delete']    = array('Delete queued message', 'Delete queued message (ID %s).');
$GLOBALS['TL_LANG']['tl_nc_queue']['show']      = array('Show details', 'Shows details of the queued message ID %s.');

/**
 * Confirmation
 */
$GLOBALS['TL_LANG']['tl_nc_queue']['re-queueConfirmation']  = 'Are you sure you want to re-add queued message ID %s to the queue?';
