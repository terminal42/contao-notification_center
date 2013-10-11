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

use NotificationCenter\Model\Language;
use NotificationCenter\Model\Notification;

class tl_nc_language extends \Backend
{

    /**
     * Save gateway type in language when creating new record
     * @param   string
     * @param   int
     * @param   array
     * @param   DataContainer
     */
    public function insertGatewayType($strTable, $insertID, $arrSet, $dc)
    {
        if ($strTable == 'tl_nc_language') {
            \Database::getInstance()->prepare("
                UPDATE tl_nc_language SET gateway_type=(SELECT type FROM tl_nc_gateway WHERE id=(SELECT gateway FROM tl_nc_message WHERE id=?)) WHERE id=?
            ")->execute($arrSet['pid'], $insertID);
        }
    }

    /**
     * Generate a list for the dcaWizard displaying the languages
     * @param   \Database_Result
     * @param   string
     * @return  string
     */
    public function generateWizardList($objRecords, $strId)
    {
        $strReturn = '
<table class="tl_listing showColumns">
<thead>
    <td class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['tl_nc_language']['language'][0] . '</td>
    <td class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['tl_nc_language']['fallback'][0] . '</td>
</thead>
<tbody>';

        $arrLanguages = \System::getLanguages();

        while ($objRecords->next()) {
            $strReturn .= '
<tr>
    <td class="tl_file_list">' . $arrLanguages[$objRecords->language] . '</td>
    <td class="tl_file_list">' . (($objRecords->fallback) ? '&#10004;' : '') . '</td>
</tr>
';
        }

        $strReturn .= '
</tbody>
</table>';

        return $strReturn;
    }
}
