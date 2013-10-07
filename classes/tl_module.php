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

class tl_module extends \Backend
{
    /**
     * Get bag choices
     * @param   \DataContainer
     * @return  array
     */
    public function getBagChoices(\DataContainer $dc)
    {
        $strWhere = '';
        $arrValues = array();
        $arrTypesToLimit = $GLOBALS['TL_DCA']['tl_module']['fields'][$dc->field]['eval']['ncBagChoices'][$dc->activeRecord->type];
        if (is_array($arrTypesToLimit) && empty($arrTypesToLimit)) {
            $strWhere .= substr(str_repeat('type=? OR ', count($arrTypesToLimit)), 0, -4);
            $arrValues = $arrTypesToLimit;
        }

        $arrChoices = array();
        $objBags = \Database::getInstance()->prepare('SELECT id,title FROM tl_nc_notification' . $strWhere . ' ORDER BY title')
                                           ->execute($arrValues);

        while ($objBags->next()) {
            $arrChoices[$objBags->id] = $objBags->title;
        }

        return $arrChoices;
    }
}