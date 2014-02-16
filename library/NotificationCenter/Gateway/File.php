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

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;


class File extends Base implements GatewayInterface
{

    /**
     * Create file
     * @param   Message
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);
            return false;
        }

        $strClass = $GLOBALS['NOTIFICATION_CENTER']['FILE'][$this->objModel->file_server_type];

        if (!class_exists($strClass)) {
            \System::log(sprintf('Could not find file server class for type "%s"', $this->objModel->file_server_type), __METHOD__, TL_ERROR);
            return false;
        }

        $objHandler = new $strClass();

        try {
            $objHandler->connect($this->objModel);
        } catch (\Exception $e) {
            \System::log(sprintf('Could not connect to the file server with error "%s"', $e->getMessage()), __METHOD__, TL_ERROR);
            return false;
        }

        $strFileName = $this->recursiveReplaceTokensAndTags($objLanguage->file_name, $arrTokens, static::NO_TAGS|static::NO_BREAKS) . '.' . $this->objModel->file_name;
        $strContent = $this->recursiveReplaceTokensAndTags($objLanguage->file_content, $arrTokens, static::NO_TAGS|static::NO_BREAKS);

        // Escape the quotes for CSV file
        if ($this->objModel->file_type == 'csv') {
            $strContent = str_replace('"', '""', $strContent);
        }

        return $objHandler->save($strFileName, $strContent, $objLanguage->file_override);
    }
}
