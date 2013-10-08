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

namespace NotificationCenter\Gateway;


class Email extends Base implements GatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function modifyDca(&$arrDca)
    {
        $strPalette = '{attachments_legend},attachments;{gateway_legend},email_sender,email_subject,email_mode,email_text';

        if ($this->objLanguage->email_mode == 'textAndHtml') {
            $strPalette .= ',email_html';
        }

        $arrDca['palettes']['default'] .= $strPalette;
    }

    /**
     * {@inheritdoc}
     */
    public function send($arrTokens)
    {
        $objMail = new \Email();

        $objMail->from      = $this->objLanguage->email_sender;
        $objMail->subject   = \String::parseSimpleTokens($this->objLanguage->email_subject, $arrTokens);
        $objMail->text      = \String::parseSimpleTokens($this->objLanguage->email_text, $arrTokens);

        if ($this->objLanguage->email_mode == 'textAndHtml') {
            $objMail->html = \String::parseSimpleTokens($this->objLanguage->email_mode, $arrTokens);
        }

        $arrAttachments = $this->getAttachments($arrTokens);

        if (!empty($arrAttachments)) {
            foreach ($arrAttachments as $strFile) {
                $objMail->attachFile($strFile);
            }
        }

        $objMail->sendTo(\String::parseSimpleTokens($this->objLanguage->recipients, $arrTokens));
    }
}