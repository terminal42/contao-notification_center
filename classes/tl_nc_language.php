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

class tl_nc_language extends \Backend
{
    /**
     * Gateway
     * @var GatewayInterface
     */
    protected $objGateway = null;

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
        if (($objNotificationModel = $objMessageModel->getRelated('pid')) === null) {
            return;
        }
        if (($objGatewayModel = $objMessageModel->getRelated('gateway')) === null) {
            return;
        }
        if (($objNotificationType = $objNotificationModel->buildNotificationType()) === null) {
            return;
        }

        $this->objGateway = $objGatewayModel->buildGateway($objNotificationType, $objMessageModel, $objLanguageModel);
        $this->objGateway->modifyDca($GLOBALS['TL_DCA'][$dc->table]);
    }

    /**
     * Label callback
     * @param   array
     * @param   string
     * @return  string
     */
    public function getLabel($arrRow, $strLabel)
    {
        if ($arrRow['fallback']) {
            $strLabel .= ' <span style="color:#ccc;">(' . $GLOBALS['TL_LANG']['tl_nc_language']['fallback'][0] . ')';
        }

        return $strLabel;
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
     * Initialize the auto suggester for recipients
     * @param   \DataContainer
     * @return  string
     */
    public function initAutoSuggesterForRecipients(\DataContainer $dc)
    {
        if (!$this->objGateway) {
            return '';
        }

        return $this->initAutoSuggester($this->objGateway->getNotificationType()->getRecipientTokens(), $dc->field);
    }

    /**
     * Initialize the auto suggester for attachments
     * @param   \DataContainer
     * @return  string
     */
    public function initAutoSuggesterForAttachments(\DataContainer $dc)
    {
        if (!$this->objGateway) {
            return '';
        }

        return $this->initAutoSuggester($this->objGateway->getNotificationType()->getAttachmentTokens(), $dc->field);
    }

    /**
     * Initialize the auto suggester for text
     * @param   \DataContainer
     * @return  string
     */
    public function initAutoSuggesterForText(\DataContainer $dc)
    {
        if (!$this->objGateway) {
            return '';
        }

        return $this->initAutoSuggester($this->objGateway->getNotificationType()->getTextTokens(), $dc->field);
    }

    /**
     * Verifiy recipient tokens
     * @param   string Text
     * @param   \DataContainer
     */
    public function verifyRecipientTokens($strText, \DataContainer $dc)
    {
        if (!$this->objGateway) {
            return $strText;
        }

        $this->verifyTokens($this->objGateway->getNotificationType()->getRecipientTokens(), $strText);

        return $strText;
    }

    /**
     * Verifiy attachment tokens
     * @param   string Text
     * @param   \DataContainer
     */
    public function verifyAttachmentTokens($strText, \DataContainer $dc)
    {
        if (!$this->objGateway) {
            return $strText;
        }

        $this->verifyTokens($this->objGateway->getNotificationType()->getAttachmentTokens(), $strText);

        return $strText;
    }

    /**
     * Verifiy text tokens
     * @param   string Text
     * @param   \DataContainer
     */
    public function verifyTextTokens($strText, \DataContainer $dc)
    {
        if (!$this->objGateway) {
            return $strText;
        }

        $this->verifyTokens($this->objGateway->getNotificationType()->getTextTokens(), $strText);

        return $strText;
    }

    /**
     * Init auto suggester
     * @param   array Tokens
     * @param   string Field name
     */
    private function initAutoSuggester($arrTokens, $strField)
    {
        $arrParsedTokens = array();

        foreach ($arrTokens as $strToken) {
            $arrParsedTokens[] = array
            (
                'value'     => $strToken,
                'content'   => $this->objGateway->getNotificationType()->getTokenDescription($strToken)
            );
        }


        $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function() {
	new AutoSuggester($('ctrl_" . $strField . "'), " . json_encode($arrParsedTokens) . ");
});
</script>";

        return '';
    }

    /**
     * Verify tokens
     * @param   array Valid tokens
     * @param   string Text
     */
    private function verifyTokens($arrValidTokens, $strText)
    {
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
    }
}