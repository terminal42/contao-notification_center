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
$GLOBALS['TL_LANG']['tl_nc_message']['title']                   = array('Title', 'Please enter a title for this message.');
$GLOBALS['TL_LANG']['tl_nc_message']['gateway']                 = array('Gateway', 'Please select a gateway for this message.');
$GLOBALS['TL_LANG']['tl_nc_message']['languages']               = array('Languages', 'Here you can manage the different languages.', 'Manage languages', 'Close');
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority']          = array('Priority', 'Please select a priority.');
$GLOBALS['TL_LANG']['tl_nc_message']['email_template']          = array('Template file', 'Please choose a template file.');
$GLOBALS['TL_LANG']['tl_nc_message']['postmark_tag']            = array('Tag', 'Here you can enter the tag.');
$GLOBALS['TL_LANG']['tl_nc_message']['postmark_trackOpens']     = array('Enable open tracking', 'Here you can enable open tracking.');
$GLOBALS['TL_LANG']['tl_nc_message']['personalized']            = array('Support personalization', 'If enabled and a personalized notification is sent, send this message for each personalized contact.');
$GLOBALS['TL_LANG']['tl_nc_message']['published']               = array('Publish message', 'Include this message when a notification is being sent.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_message']['new']                     = array('New message', 'Create a new message.');
$GLOBALS['TL_LANG']['tl_nc_message']['edit']                    = array('Edit message', 'Edit message ID %s.');
$GLOBALS['TL_LANG']['tl_nc_message']['copy']                    = array('Duplicate message', 'Duplicate message ID %s.');
$GLOBALS['TL_LANG']['tl_nc_message']['cut']                     = array('Move message', 'Move message ID %s.');
$GLOBALS['TL_LANG']['tl_nc_message']['delete']                  = array('Delete message', 'Delete message ID %s.');
$GLOBALS['TL_LANG']['tl_nc_message']['toggle']                  = array('Toggle visibility of message', 'Toggle visibility of message ID %s.');
$GLOBALS['TL_LANG']['tl_nc_message']['show']                    = array('Gateway message', 'Show details for message ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_message']['title_legend']            = 'Title & Gateway';
$GLOBALS['TL_LANG']['tl_nc_message']['languages_legend']        = 'Languages';
$GLOBALS['TL_LANG']['tl_nc_message']['expert_legend']           = 'Expert settings';
$GLOBALS['TL_LANG']['tl_nc_message']['publish_legend']          = 'Publish settings';

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options']['1'] = 'very high';
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options']['2'] = 'high';
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options']['3'] = 'normal';
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options']['4'] = 'low';
$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options']['5'] = 'very low';
