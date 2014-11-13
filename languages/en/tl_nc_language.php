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
$GLOBALS['TL_LANG']['tl_nc_language']['language']                   = array('Language', 'Please select a language.');
$GLOBALS['TL_LANG']['tl_nc_language']['fallback']                   = array('Fallback', 'Activate this checkbox if this language should be your fallback.');
$GLOBALS['TL_LANG']['tl_nc_language']['recipients']                 = array('Recipients', 'Please enter a <strong>comma-separated</strong> list of recipients in this field. Use the autocompleter to see the available simple tokens.');
$GLOBALS['TL_LANG']['tl_nc_language']['attachment_tokens']          = array('Attachments via tokens', 'Please enter a <strong>comma-separated</strong> list of attachment tokens in this field. Use the autocompleter to see the available simple tokens.');
$GLOBALS['TL_LANG']['tl_nc_language']['attachments']                = array('Attachments from file system', 'Please choose from the file picker if you would like to add static files.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_name']          = array('Sender name', 'Please enter the sender name.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_address']       = array('Sender address', 'Please enter the sender email address.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_cc']         = array('Send a CC to', 'Recipients that should receive a carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_bcc']        = array('Send a BCC to', 'Recipients that should receive a blind carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_replyTo']              = array('Reply-to address', 'You can optionally set a reply-to address for this message.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_subject']              = array('Subject', 'Please enter the subject for the e-mail.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']                 = array('Mode', 'Choose the mode you would like to be used for this email.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_text']                 = array('Raw text', 'Please enter the text.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_html']                 = array('HTML', 'Please enter the HTML.');
$GLOBALS['TL_LANG']['tl_nc_language']['file_name']                  = array('File name', 'Please enter the file name without extension.');
$GLOBALS['TL_LANG']['tl_nc_language']['file_override']              = array('Override existing file', 'Override the old file if it exists.');
$GLOBALS['TL_LANG']['tl_nc_language']['file_content']               = array('File content', 'Please enter the file content.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textOnly']     = 'Text only';
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textAndHtml']  = 'HTML and text';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_language']['new']                        = array('New language', 'Add a new language.');
$GLOBALS['TL_LANG']['tl_nc_language']['edit']                       = array('Edit language', 'Edit language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['copy']                       = array('Copy language', 'Copy language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['delete']                     = array('Delete language', 'Delete language ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['show']                       = array('Language details', 'Show details for language ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_language']['general_legend']             = 'General language settings';
$GLOBALS['TL_LANG']['tl_nc_language']['attachments_legend']         = 'Attachments';
$GLOBALS['TL_LANG']['tl_nc_language']['meta_legend']                = 'Meta information';
$GLOBALS['TL_LANG']['tl_nc_language']['content_legend']             = 'Content';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['tl_nc_language']['token_error']                = 'The following tokens you have used are not supported by this notification type: %s.';