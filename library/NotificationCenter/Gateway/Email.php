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

        $objEmail = new \Email();
        $objEmail->priority = $objMessage->email_priority;

        // Set optional sender name
        $strSenderName = $objLanguage->email_sender_name ?: $GLOBALS['TL_ADMIN_NAME'];
        if ($strSenderName != '') {
            $strSenderName = $this->recursiveReplaceTokensAndTags($strSenderName, $arrTokens);
            $objEmail->fromName = strip_tags($strSenderName);
        }

        // Set email sender address
        $strSenderAddress = $objLanguage->email_sender_address ?: $GLOBALS['TL_ADMIN_EMAIL'];
        $strSenderAddress = $this->recursiveReplaceTokensAndTags($strSenderAddress, $arrTokens);
        $objEmail->from = strip_tags($strSenderAddress);

        // Set email subject
        $strSubject = $objLanguage->email_subject;
        $strSubject = $this->recursiveReplaceTokensAndTags($strSubject, $arrTokens);
        $objEmail->subject = strip_tags($strSubject);

        // Set email text content
        $strText = $objLanguage->email_text;
        $strText = $this->recursiveReplaceTokensAndTags($strText, $arrTokens);
        $strText = \Controller::convertRelativeUrls($strText, '', true);
        $objEmail->text = strip_tags($strText);

        // Set optional email HTML content
        if ($objLanguage->email_mode == 'textAndHtml')
        {
            $objTemplate = new \FrontendTemplate($this->email_template);
            $objTemplate->body = $objLanguage->email_html;
            $objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];

            // Prevent parseSimpleTokens from stripping important HTML tags
            $GLOBALS['TL_CONFIG']['allowedTags'] .= '<doctype><html><head><meta><style><body>';
            $strHtml = str_replace('<!DOCTYPE', '<DOCTYPE', $objTemplate->parse());
            $strHtml = $this->recursiveReplaceTokensAndTags($strHtml, $arrTokens);
            $strHtml = \Controller::convertRelativeUrls($strHtml, '', true);
            $strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);

            // Parse template
            $objEmail->html = $strHtml;
            $objEmail->imageDir = TL_ROOT . '/';
        }

        // Add all token attachments
        $arrTokenAttachments = $this->getTokenAttachments($objLanguage->attachment_tokens, $arrTokens);
        if (!empty($arrTokenAttachments)) {
            foreach ($arrTokenAttachments as $strFile) {
                $objEmail->attachFile($strFile);
            }
        }

        // Add static attachments
        $arrAttachments = deserialize($objLanguage->attachments);

        if (is_array($arrAttachments) && !empty($arrAttachments)) {
            $objFiles = \FilesModel::findMultipleByUuids($arrAttachments);
            while ($objFiles->next()) {
                $objEmail->attachFile($objFiles->path);
            }
        }

        // Set CC recipients
        $arrCc = $this->compileRecipients($objLanguage->email_recipient_cc, $arrTokens);
        if (!empty($arrCc)) {
            $objEmail->sendCc($arrCc);
        }

        // Set BCC recipients
        $arrBcc = $this->compileRecipients($objLanguage->email_recipient_bcc, $arrTokens);
        if (!empty($arrBcc)) {
            $objEmail->sendBcc($arrBcc);
        }

        try {
            return $objEmail->sendTo($this->recursiveReplaceTokensAndTags($objLanguage->recipients, $arrTokens));
        } catch(\Exception $e) {
            \System::log(sprintf('Could not send email for message ID %s: %s', $objMessage->id, $e->getMessage()), __METHOD__, TL_ERROR);
        }

        return false;
    }
}
