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

class tl_nc_notification extends \Backend
{
    /**
     * Get all registered notification types
     * @return  array
     */
    public function getNotificationTypes()
    {
        $arrNotificationTypes = array();

        if (!empty($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE']) && is_array($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE'])) {
            foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE'] as $k=>$v) {
                foreach (array_keys($v) as $kk) {
                    $arrNotificationTypes[$k][] = $kk;
                }
            }
        }

        return $arrNotificationTypes;
    }

    /**
     * Label callback
     * @param   array
     * @param   string
     * @return  string
     */
    public function getLabel($arrRow, $strLabel)
    {
        $strGroup = '';
        $strType = '';

        if (!empty($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE']) && is_array($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE'])) {
            foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE'] as $k=>$v) {
                foreach (array_keys($v) as $kk) {

                    if ($kk == $arrRow['type']) {
                        $strGroup = $GLOBALS['TL_LANG']['tl_nc_notification']['type'][$k];
                        $strType  = $GLOBALS['TL_LANG']['tl_nc_notification']['type'][$kk][0];
                    }
                }
            }
        }

        if ($strGroup && $strType) {
            $strLabel .= ' <span style="color:#ccc;">(' . $strGroup . ': ' . $strType . ')';
        }

        return $strLabel;
    }

}