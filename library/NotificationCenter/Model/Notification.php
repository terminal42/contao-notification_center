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

class Notification extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_notification';

    /**
     * Gets the published notifications collection
     * @return Message[]
     */
    public function getMessages()
    {
        return Message::findPublishedByNotification($this);
    }

    /**
     * Sends a notification
     * @param   array   The tokens
     * @param   string  The language (optional)
     * @return  array
     */
    public function send(array $arrTokens, $strLanguage = '')
    {
        // Check if there are valid messages
        if (($objMessages = $this->getMessages()) === null) {
            \System::log('Could not find any messages for notification ID ' . $this->id, __METHOD__, TL_ERROR);

            return array();
        }

        $arrResult = array();

        foreach ($objMessages as $objMessage) {
            $arrResult[$objMessage->id] = $objMessage->send($arrTokens, $strLanguage);
        }

        return $arrResult;
    }

    /**
     * Find notification group for type
     * @param   string Type
     * @return  string Class
     */
    public static function findGroupForType($strType)
    {
        foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $strGroup => $arrTypes) {
            if (in_array($strType, array_keys($arrTypes))) {
                return $strGroup;
            }
        }

        return '';
    }
}
