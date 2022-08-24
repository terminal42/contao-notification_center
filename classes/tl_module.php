<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

class tl_module extends \Backend
{
    /**
     * Get notification choices
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getNotificationChoices(\DataContainer $dc)
    {
        $strWhere = '';
        $arrValues = array();
        $arrTypes = $GLOBALS['TL_DCA']['tl_module']['fields'][$dc->field]['eval']['ncNotificationChoices'][$dc->activeRecord->type] ?? [];

        if (!empty($arrTypes) && is_array($arrTypes)) {
            $strWhere = ' WHERE ' . implode(' OR ', array_fill(0, count($arrTypes), 'type=?'));
            $arrValues = $arrTypes;
        }

        $arrChoices = array();
        $objNotifications = \Database::getInstance()->prepare('SELECT id,title FROM tl_nc_notification' . $strWhere . ' ORDER BY title')
                                           ->execute($arrValues);

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }
}
