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

use NotificationCenter\MessageDraft\MessageDraftFactoryInterface;
use NotificationCenter\MessageDraft\PostmarkMessageDraft;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;


class Postmark extends Base implements GatewayInterface, MessageDraftFactoryInterface
{

    /**
     * Returns a MessageDraft
     * @param   Message
     * @param   array
     * @param   string
     * @return  MessageDraftInterface|null (if no draft could be found)
     */
    public function createDraft(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);

            return null;
        }

        return new PostmarkMessageDraft($objMessage, $objLanguage, $arrTokens);
    }

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

        /**
         * @var $objDraft \NotificationCenter\MessageDraft\PostmarkMessageDraft
         */
        $objDraft = $this->createDraft($objMessage, $arrTokens, $strLanguage);

        // return false if no language found for BC
        if ($objDraft === null) {
            return false;
        }

        $strFrom = $objDraft->getSenderEmail();
        // Generate friendly name from address if possible
        if ($strSenderName = $objDraft->getSenderName()) {
            // Don't do this if the sender name contains the email address
            if ($strFrom !== $strSenderName) {
                $strFrom = $strSenderName . ' <' . $strFrom . '>';
            }
        }

        // Recipients
        $arrTo = array_slice($objDraft->getRecipientEmails(), 0, 20);

        // Set basic data
        $arrData = array
        (
            'From'          => $strFrom,
            'To'            => implode(',', $arrTo),
            'Subject'       => $objDraft->getSubject(),
            'HtmlBody'      => $objDraft->getHtmlBody(),
            'TextBody'      => $objDraft->getTextBody(),
            'TrackOpens'    => $objDraft->getTrackOpen()
        );

        // Set CC recipients
        if (!empty($arrCc)) {
            $arrData['Cc'] = implode(',', array_slice($objDraft->getCcRecipientEmails(), 0, 20));
        }

        // Set BCC recipients
        if (!empty($arrBcc)) {
            $arrData['Bcc'] = implode(',', array_slice($objDraft->getBccRecipientEmails(), 0, 20));
        }

        // Set reply-to address
        if ($strReplyTo = $objDraft->getReplyToEmail()) {
            $arrData['ReplyTo'] = $strReplyTo;
        }

        // Set the Postmark tag
        if ($strTag = $objDraft->getTag()) {
            $arrData['Tag'] = $strTag;
        }

        $strData = json_encode($arrData);

        $objRequest = new \Request();
        $objRequest->setHeader('Content-Type', 'application/json');
        $objRequest->setHeader('X-Postmark-Server-Token', ($this->objModel->postmark_test ? 'POSTMARK_API_TEST' : $this->objModel->postmark_key));
        $objRequest->send(($this->objModel->postmark_ssl ? 'https://' : 'http://') . 'api.postmarkapp.com/email', $strData, 'POST');

        if ($objRequest->hasError()) {
            \System::log(
                sprintf('Error sending the Postmark request for message ID "%s". HTTP Response status code: %s. JSON data sent: %s',
                    $objMessage->id,
                    $objRequest->code,
                    $strData
                ),
                __METHOD__,
                TL_ERROR);
            return false;
        } else {
            $strWouldHave = ($this->objModel->postmark_test) ? ' would have (test mode)' : '';
            \System::log(
                sprintf('The Postmark API accepted the request and%s sent %s emails. HTTP Response status code: %s. JSON data sent: %s',
                    $strWouldHave,
                    count($arrTo),
                    $objRequest->code,
                    $strData
                ),
                __METHOD__,
                TL_GENERAL);
        }

        return true;
    }
}
