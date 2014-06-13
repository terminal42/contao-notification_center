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

use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;


class Postmark extends Base implements GatewayInterface
{

    /**
     * Send Postmark request message
     * @param   Message
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ($this->objModel->postmark_key == '') {
            \System::log(sprintf('Please provide the Postmark API key for message ID "%s"', $objMessage->id), __METHOD__, TL_ERROR);
            return false;
        }

        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);

            return false;
        }

        $strSenderName = $objLanguage->email_sender_name ? : $GLOBALS['TL_ADMIN_NAME'];
        $strSenderAddress = $objLanguage->email_sender_address ? : $GLOBALS['TL_ADMIN_EMAIL'];

        // Get email text content
        $strText = $objLanguage->email_text;
        $strText = $this->recursiveReplaceTokensAndTags($strText, $arrTokens, static::NO_TAGS);
        $strText =  \Controller::convertRelativeUrls($strText, '', true);

        // Get HTML
        $objTemplate          = new \FrontendTemplate($objMessage->email_template);
        $objTemplate->body    = $objLanguage->email_html;
        $objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];

        // Prevent parseSimpleTokens from stripping important HTML tags
        $GLOBALS['TL_CONFIG']['allowedTags'] .= '<doctype><html><head><meta><style><body>';
        $strHtml = str_replace('<!DOCTYPE', '<DOCTYPE', $objTemplate->parse());
        $strHtml = $this->recursiveReplaceTokensAndTags($strHtml, $arrTokens);
        $strHtml = \Controller::convertRelativeUrls($strHtml, '', true);
        $strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);

        // Set basic data
        $arrData = array
        (
            'From' => ($strSenderName ? ($strSenderName . ' ') : '') . $this->recursiveReplaceTokensAndTags($strSenderAddress, $arrTokens, static::NO_TAGS|static::NO_BREAKS),
            'To' => $this->recursiveReplaceTokensAndTags($objLanguage->recipients, $arrTokens, static::NO_TAGS|static::NO_BREAKS),
            'Subject' => $this->recursiveReplaceTokensAndTags($objLanguage->email_subject, $arrTokens, static::NO_TAGS|static::NO_BREAKS),
            'HtmlBody' => $strHtml,
            'TextBody' => $strText,
            'TrackOpens' => $objMessage->postmark_trackOpens ? true : false
        );

        $arrCc = $this->compileRecipients($objLanguage->email_recipient_cc, $arrTokens);

        // Set CC recipients
        if (!empty($arrCc)) {
            $arrData['Cc'] = implode(',', array_slice($arrCc, 0, 20));
        }

        $arrBcc = $this->compileRecipients($objLanguage->email_recipient_bcc, $arrTokens);

        // Set BCC recipients
        if (!empty($arrBcc)) {
            $arrData['Bcc'] = implode(',', array_slice($arrBcc, 0, 20));
        }

        // Set reply-to address
        if ($objLanguage->email_replyTo) {
            $arrData['ReplyTo'] = $this->recursiveReplaceTokensAndTags($objLanguage->email_replyTo, $arrTokens, static::NO_TAGS|static::NO_BREAKS);
        }

        // Set the Postmark tag
        if ($objMessage->postmark_tag != '') {
            $arrData['Tag'] = $objMessage->postmark_tag;
        }

        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/json');
        $objRequest->setHeader('X-Postmark-Server-Token', ($this->objModel->postmark_test ? 'POSTMARK_API_TEST' : $this->objModel->postmark_key));
        $objRequest->send(($this->objModel->postmark_ssl ? 'https://' : 'http://') . 'api.postmarkapp.com/email', json_encode($arrData), 'POST');

        if ($objRequest->hasError()) {
            \System::log(sprintf('Error sending the Postmark request for message ID "%s": %s', $objMessage->id, $objRequest->error), __METHOD__, TL_ERROR);
            return false;
        }

        return true;
    }
}
