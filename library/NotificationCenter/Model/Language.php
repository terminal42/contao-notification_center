<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

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

        // Support the language variations (e.g. en-US)
        $strLanguage = str_replace('-', '_', $strLanguage);

        $arrColumns = array("$t.pid=?", "($t.language=? OR $t.fallback=1)");
        $arrValues  = array($objMessage->id, $strLanguage);
        $arrOptions = array('order' => 'fallback');

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }
}
