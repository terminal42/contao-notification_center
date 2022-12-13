<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
        $arrTo  = $objDraft->getRecipientEmails();
        $arrCc  = $objDraft->getCcRecipientEmails();
        $arrBcc = $objDraft->getBccRecipientEmails();

        if (count(array_merge($arrTo, $arrCc, $arrBcc)) >= 20) {
            $this->objModel->addFormError();
            \System::log(
                sprintf('The Postmark gateway does not support sending to more than 20 recipients (CC and BCC included) for message ID "%s".',
                    $objMessage->id
                ),
                __METHOD__,
                TL_ERROR);

            return false;
        }

        // Set basic data
        $arrData = array
        (
            'From'       => $strFrom,
            'To'         => implode(',', $arrTo),
            'Subject'    => $objDraft->getSubject(),
            'HtmlBody'   => $objDraft->getHtmlBody(),
            'TextBody'   => $objDraft->getTextBody(),
            'TrackOpens' => $objDraft->getTrackOpen()
        );

        // Set CC recipients
        if (!empty($arrCc)) {
            $arrData['Cc'] = implode(',', $arrCc);
        }

        // Set BCC recipients
        if (!empty($arrBcc)) {
            $arrData['Bcc'] = implode(',', $arrBcc);
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

        // Postmark uses HTTP status code 10 for wrong API keys. The contao request class cannot handle this and thus returns 0.
        $code = $objRequest->code;
        if ($code == 0) {
            $code = 10;
        }

        if ($objRequest->hasError()) {
            $this->objModel->addFormError();
            \System::log(
                sprintf('Error sending the Postmark request for message ID "%s". HTTP Response status code: %s. JSON data sent: %s',
                    $objMessage->id,
                    $code,
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
                    $code,
                    $strData
                ),
                __METHOD__,
                TL_GENERAL);
        }

        return true;
    }
}
