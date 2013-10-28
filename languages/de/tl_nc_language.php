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

$GLOBALS['TL_LANG']['tl_nc_language']['language']                   = array('Sprache', 'Bitte wähle eine Sprache.');
$GLOBALS['TL_LANG']['tl_nc_language']['fallback']                   = array('Fallback-Sprache', 'Markiere die Checkbox falls dies die Fallback-Sprache ist.');
$GLOBALS['TL_LANG']['tl_nc_language']['recipients']                 = array('Empfänger', 'Bitte gebe eine <strong>Komma-separierte</strong> Liste der Empfänger in dieses Feld ein. Nutze den "help wizard" um die verfügbaren simple tokens zu sehen.');
$GLOBALS['TL_LANG']['tl_nc_language']['attachment_tokens']          = array('Attachments via tokens', 'Bitte gebe eine <strong>Komma-separierte</strong> Liste von Attachement-Tokens eingeben of attachment tokens in this field. Nutze den "help wizard" um die verfügbaren simple tokens zu sehen.');
$GLOBALS['TL_LANG']['tl_nc_language']['attachments']                = array('Attachments vom Filesystem', 'Please wähle mit dem Filepicker die statischen Dateien die du ans Mail anfügen willst.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_name']          = array('Absendername', 'Bitte gebe den Absender ein.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_address']       = array('Absender-Email', 'Bitte gebe die Absender-Adresse ein.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_cc']         = array('CC-Empfänger', 'Emptänger die eine Kopie des Mails erhalten sollten. Mehrere Adressen per Komma separieren.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_bcc']        = array('BCC-Empfänger', 'Empfänger die eine Blindkopie des Mails erhalten sollten. Mehrere Adressen per Komma separieren.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_subject']              = array('Subject', 'Bite den Betreff für das Email eingeben.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']                 = array('Modus', 'Bitte den gewünschten Modus für das Email wählen.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_text']                 = array('Rohtext', 'Bitte den Text eingeben.');
$GLOBALS['TL_LANG']['tl_nc_language']['email_html']                 = array('HTML', 'Bitte HTML eingeben.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textOnly']     = 'Nur Text';
$GLOBALS['TL_LANG']['tl_nc_language']['email_mode']['textAndHtml']  = 'HTML und Text';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_nc_language']['new']                     = array('Neue Sprache', 'Füge eine neue Sprache hinzu.');
$GLOBALS['TL_LANG']['tl_nc_language']['edit']                    = array('Sprache editieren', 'Sprache ID %s editieren.');
$GLOBALS['TL_LANG']['tl_nc_language']['copy']                    = array('Sprache kopieren', 'Kopiere Sprache ID %s.');
$GLOBALS['TL_LANG']['tl_nc_language']['delete']                  = array('Sprache löschen', 'Sprache mit ID %s löschen.');
$GLOBALS['TL_LANG']['tl_nc_language']['show']                    = array('Sprach-Details', 'Details für ID %s anzeigen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_nc_language']['general_legend']             = 'Generelle Spracheinstellungen';
$GLOBALS['TL_LANG']['tl_nc_language']['attachments_legend']         = 'Attachments';
$GLOBALS['TL_LANG']['tl_nc_language']['meta_legend']                = 'Meta-Informationen';
$GLOBALS['TL_LANG']['tl_nc_language']['content_legend']             = 'Content';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['tl_nc_language']['token_error']                = 'Die folgenden eingesetzten Tokens werden vom Notification-Typ nicht unterstützt: %s.';
