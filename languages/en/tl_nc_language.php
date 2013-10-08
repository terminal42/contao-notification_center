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
$GLOBALS['TL_LANG']['tl_nc_language']['language']                = array('Language', 'Please select a language.');
$GLOBALS['TL_LANG']['tl_nc_language']['fallback']                = array('Fallback', 'Activate this checkbox if this language should be your fallback.');
$GLOBALS['TL_LANG']['tl_nc_language']['recipients']              = array('Recipients', 'Please enter the recipients in this field. Use the help wizard to see the available simple tokens.');
$GLOBALS['TL_LANG']['tl_nc_language']['attachments']             = array('Attachments', 'Please enter a <strong>comma-separated</strong> list of attachment tokens in this field. Use the help wizard to see the available simple tokens.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_sender']            = array('Sender', 'Please enter the sender of the e-mail.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_subject']           = array('Subject', 'Please enter the subject for the e-mail.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']              = array('Mode', 'Choose the mode you would like to be used for this email.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_text']              = array('Raw text', 'Please enter the text.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_html']              = array('HTML', 'Please enter the HTML.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textOnly']     = 'Text only';
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textAndHtml']  = 'HTML and text';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_language']['new']                     = array('New language', 'Add a new language.');
$GLOBALS['TL_LANG']['tl_nc_language']['edit']                    = array('Edit language', 'Edit language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['copy']                    = array('Copy language', 'Copy language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['delete']                  = array('Delete language', 'Delete language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['show']                    = array('Language details', 'Show details for language ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_language']['general_legend']          = 'General language settings';
$GLOBALS['TL_LANG']['tl_nc_language']['attachments_legend']      = 'Attachments';
$GLOBALS['TL_LANG']['tl_nc_language']['gateway_legend']          = 'Gateway settings';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['tl_nc_language']['token_error']             = 'The following tokens you have used are not supported by this notification type: %s.';