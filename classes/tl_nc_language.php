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
     * Notification moel
     * @var Notification
     */
    protected $objNotificationModel = null;

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
     * Loads gateway
     * @param   \DataContainer
     */
    public function loadGateway(\DataContainer $dc)
    {
        if (($objLanguageModel = Language::findByPk($dc->id)) === null) {
            return;
        }

        if (($objMessageModel = $objLanguageModel->getRelated('pid')) === null) {
            return;
        }
        if (($this->objNotificationModel = $objMessageModel->getRelated('pid')) === null) {
            return;
        }
        if (($objGatewayModel = $objMessageModel->getRelated('gateway')) === null) {
            return;
        }

        $objGateway = $objGatewayModel->buildGateway($objMessageModel, $objLanguageModel);
        $objGateway->modifyDca($GLOBALS['TL_DCA'][$dc->table]);
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

    /**
     * Initialize the auto suggester
     * @param   \DataContainer
     * @return  string
     */
    public function initAutoSuggester(\DataContainer $dc)
    {
        \System::loadLanguageFile('tokens');
        $strType = $this->objNotificationModel->type;
        $strGroup = Notification::findGroupForType($strType);
        $arrTokens = $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strGroup][$strType][$dc->field];

        $arrParsedTokens = array();
        foreach ($arrTokens as $strToken) {
            $arrParsedTokens[] = array
            (
                'value'     => $strToken,
                'content'   => $GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][$strType][$strToken] ?: ''
            );
        }


        $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function() {
	new AutoSuggester($('ctrl_" . $dc->field . "'), " . json_encode($arrParsedTokens) . ");
});
</script>";

        return '';
    }

    /**
     * Verify tokens
     * @param   string Text
     * @param   \DataContainer
     */
    public function verifyTokens($strText, \DataContainer $dc)
    {
        $strType = $this->objNotificationModel->type;
        $strGroup = Notification::findGroupForType($strType);
        $arrValidTokens = $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strGroup][$strType][$dc->field];

        // Build regex pattern
        $strPattern = '/##(' . implode('|', $arrValidTokens) . ')##/i';
        $strPattern = str_replace('*', '[^##]*', $strPattern);

        preg_match_all($strPattern, $strText, $arrValidMatches);
        preg_match_all('/##([A-Za-z0-9_]+)##/i', $strText, $arrAllMatches);

        $arrInvalidTokens = array_diff($arrAllMatches[1], $arrValidMatches[1]);

        if (count($arrInvalidTokens)) {
            $strInvalidTokens = '##' . implode('##, ##', $arrInvalidTokens) . '##';
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_nc_language']['token_error'], $strInvalidTokens));
        }

        return $strText;
    }
}
