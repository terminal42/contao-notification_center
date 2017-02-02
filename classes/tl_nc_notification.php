<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

class tl_nc_notification extends \Backend
{
    /**
     * Get all registered notification types
     *
     * @return array
     */
    public function getNotificationTypes()
    {
        $arrNotificationTypes = array();

        if (!empty($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']) && is_array($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'])) {
            foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $k => $v) {
                foreach ($v as $kk => $vv) {
                    $arrNotificationTypes[$k][] = $kk;
                }
            }
        }

        return $arrNotificationTypes;
    }

    /**
     * Label callback
     *
     * @param string         $strLabel
     * @param int            $intMode
     * @param string         $strField
     * @param array          $arrRow
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function getGroupLabel($strLabel, $intMode, $strField, $arrRow, $dc)
    {
        $strGroup = '';
        $strType = '';

        if (!empty($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']) && is_array($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'])) {
            foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $k => $v) {
                foreach ($v as $kk => $vv) {

                    if ($kk == $arrRow['type']) {
                        $strGroup = $GLOBALS['TL_LANG']['tl_nc_notification']['type'][$k];
                        $strType  = $GLOBALS['TL_LANG']['tl_nc_notification']['type'][$kk][0];
                    }
                }
            }
        }

        if ($strGroup && $strType) {
            $strLabel = $strGroup . ': ' . $strType;
        }

        return $strLabel;
    }

}