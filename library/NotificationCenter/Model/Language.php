<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $gateway_type
 * @property string $language
 * @property string $fallback
 * @property string $recipients
 * @property string $attachment_tokens
 * @property string $attachments
 * @property string $email_sender_name
 * @property string $email_sender_address
 * @property string $email_recipient_cc
 * @property string $email_recipient_bcc
 * @property string $email_replyTo
 * @property string $email_subject
 * @property string $email_mode
 * @property string $email_text
 * @property string $email_html
 * @property string $file_name
 * @property string $file_storage_mode
 * @property string $file_content
 */
class Language extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_language';

    /**
     * Find by message and language or fallback
     * @param   Message
     * @param   string Language
     */
    public static function findByMessageAndLanguageOrFallback(Message $objMessage, $strLanguage)
    {
        $t = static::$strTable;

        $arrColumns = array("$t.pid=?", "($t.language=? OR $t.fallback=1)");
        $arrValues  = array($objMessage->id, $strLanguage);
        $arrOptions = array('order' => 'fallback');

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }
}
