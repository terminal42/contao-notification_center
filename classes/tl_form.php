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

class tl_form extends \Backend
{
    /**
     * Get notification choices
     * @param   \DataContainer
     * @return  array
     */
    public function getNotificationChoices(\DataContainer $dc)
    {
        $arrChoices = array();
        $objNotifications = \Database::getInstance()->execute("SELECT id,title FROM tl_nc_notification WHERE type='core_form' ORDER BY title");

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }

    /**
     * Send the form notification
     * @param array
     * @param array
     */
    public function sendFormNotification($arrData, $arrForm)
    {
        if (!$arrForm['nc_notification'] || ($objNotification = Model\Notification::findByPk($arrForm['nc_notification'])) === null) {
            return;
        }

        $arrTokens = array();

        foreach ($arrData as $k => $v) {
            $this->flatten($v, 'form_'.$k, $arrTokens);
        }

        foreach ($arrForm as $k => $v) {
            $this->flatten($v, 'formconfig_'.$k, $arrTokens);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        $objNotification->send($arrTokens);
    }

    /**
     * Flatten input data, Simple Tokens can't handle arrays
     * @param   mixed
     * @param   string
     * @param   array
     */
    public function flatten($varValue, $strKey, &$arrData)
    {
        if (!is_array($varValue)) {
            $arrData[$strKey] = $varValue;
            return;
        }

        $blnAssoc = array_is_assoc($varValue);

        foreach ($varValue as $k => $v) {
            if ($blnAssoc && !is_array($v)) {
                $this->flatten($v, $strKey.'_'.$k, $arrData);
            } else {
                $arrData[$strKey.'_'.$v] = '1';
            }
        }
    }
}
