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

        $arrColumns = array("$t.pid=?", "($t.language=? OR $t.fallback=1)");
        $arrValues  = array($objMessage->id, $strLanguage);
        $arrOptions = array('order' => 'fallback');

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }
}
