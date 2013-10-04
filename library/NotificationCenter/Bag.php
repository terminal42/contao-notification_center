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

namespace NotificationCenter;

use NotificationCenter\Model\Bag as BagModel;

class Bag
{
    /**
     * Sends a notification bag
     * @param   int The notification bag ID
     * @param   array The tokens
     * @param   string The language (optional)
     * @return  boolean
     */
    public static function send($intBagId, $arrTokens, $strLanguage='')
    {
        // Check if this is a valid Bag model id
        if (($objBag = BagModel::findByPk($intBagId)) === null) {
            \System::log(sprintf(
                    'Could not find notification bag ID "%s".',
                    $intBagId
                    ),
                __METHOD__,
                TL_ERROR);
            return false;
        }

        // Check if there are valid notifications
        if (($objNotifications = $objBag->getNotifications()) === null) {
            \System::log(sprintf(
                    'Could not find any notifications for notification bag ID "%s".',
                    $intBagId
                ),
                __METHOD__,
                TL_ERROR);
            return false;
        }

        // Check if there is a valid bag type
        if (($objBagType = $objBag->buildBagType()) === null) {
            \System::log(sprintf(
                    'Could not build bag type for notification bag ID "%s".',
                    $intBagId
                ),
                __METHOD__,
                TL_ERROR);
            return false;
        }

        // Set default language
        if ($strLanguage === '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        $blnHasError = false;

        while ($objNotifications->next()) {
            $objNotification = $objNotifications->current();

            if (($objGateway = $objNotification->buildGateway($objBagType, $strLanguage)) == null) {
                \System::log(sprintf(
                        'Could not build gateway for notification ID "%s".',
                        $objNotification->id
                    ),
                    __METHOD__,
                    TL_ERROR);
                $blnHasError = true;
            }

            $objGateway->send($arrTokens);
        }

        return !$blnHasError;
    }
}