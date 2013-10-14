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

use NotificationCenter\Model\Message;
use NotificationCenter\Model\Language;


class Email extends Base implements GatewayInterface
{

    /**
     * Send email message
     * @param   Message
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage='')
    {
        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);
            return false;
        }

        $objMail = new \Email();

        list($objMail->fromName, $objMail->from) = \String::splitFriendlyEmail(\String::parseSimpleTokens($objLanguage->email_sender, $arrTokens));

        $objMail->subject   = \String::parseSimpleTokens($objLanguage->email_subject, $arrTokens);
        $objMail->text      = \String::parseSimpleTokens($objLanguage->email_text, $arrTokens);

        if ($objLanguage->email_mode == 'textAndHtml') {
            $objMail->html = \String::parseSimpleTokens($objLanguage->email_mode, $arrTokens);
        }

        $arrAttachments = $this->getAttachments($objLanguage->attachments, $arrTokens);

        if (!empty($arrAttachments)) {
            foreach ($arrAttachments as $strFile) {
                $objMail->attachFile($strFile);
            }
        }

        try {
            return $objMail->sendTo(\String::parseSimpleTokens($objLanguage->recipients, $arrTokens));
        } catch(\Exception $e) {
            \System::log(sprintf('Could not send email for message ID %s: %s', $objMessage->id, $e->getMessage()), __METHOD__, TL_ERROR);
        }

        return false;
    }
}
