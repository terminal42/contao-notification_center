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
 * @copyright  terminal42 gmbh 2014
 * @license    LGPL
 */

namespace NotificationCenter\Gateway;

use NotificationCenter\MessageDraft\EmailMessageDraft;
use NotificationCenter\MessageDraft\MessageDraftFactoryInterface;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;


class Email extends Base implements GatewayInterface, MessageDraftFactoryInterface
{
    /**
     * SMTP settings cache
     * @var array
     */
    protected $arrSMTPCache = array();

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

        return new EmailMessageDraft($objMessage, $objLanguage, $arrTokens);
    }

    /**
     * Send email message
     * @param   Message
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        /**
         * @var $objDraft \NotificationCenter\MessageDraft\EmailMessageDraft
         */
        $objDraft = $this->createDraft($objMessage, $arrTokens, $strLanguage);

        // return false if no language found for BC
        if ($objDraft === null) {
            return false;
        }

        // Override SMTP settings if desired
        $this->overrideSMTPSettings();
        $objEmail = new \Email();
        $this->resetSMTPSettings();

        // Set priority
        $objEmail->priority = $objDraft->getPriority();

        // Set optional sender name
        if ($strSenderName = $objDraft->getSenderName()) {
            $objEmail->fromName = $strSenderName;
        }

        // Set email sender address
        $objEmail->from   = $objDraft->getSenderEmail();

        // Set reply-to address
        if ($strReplyTo = $objDraft->getReplyToEmail()) {
            $objEmail->replyTo($strReplyTo);
        }

        // Set email subject
        $objEmail->subject = $objDraft->getSubject();

        // Set email text content
        $objEmail->text = $objDraft->getTextBody();

        // Set optional email HTML content
        if ($strHtml = $objDraft->getHtmlBody()) {
            $objEmail->html     = $strHtml;
            $objEmail->imageDir = TL_ROOT . '/';
        }

        // Add attachments
        $arrAttachments = $objDraft->getAttachments();
        if (!empty($arrAttachments)) {
            foreach ($arrAttachments as $strFile) {
                $objEmail->attachFile($strFile);
            }
        }

        // Set CC recipients
        $arrCc = $objDraft->getCcRecipientEmails();
        if (!empty($arrCc)) {
            $objEmail->sendCc($arrCc);
        }

        // Set BCC recipients
        $arrBcc = $objDraft->getBccRecipientEmails();
        if (!empty($arrBcc)) {
            $objEmail->sendBcc($arrBcc);
        }

        try {
            return $objEmail->sendTo($objDraft->getRecipientEmails());
        } catch (\Exception $e) {
            \System::log(sprintf('Could not send email for message ID %s: %s', $objMessage->id, $e->getMessage()), __METHOD__, TL_ERROR);
        }

        return false;
    }

    /**
     * Override SMTP settings
     */
    protected function overrideSMTPSettings()
    {
        if (!$this->objModel->email_overrideSmtp) {
            return;
        }

        $this->arrSMTPCache['useSMTP'] = $GLOBALS['TL_CONFIG']['useSMTP'];
        $GLOBALS['TL_CONFIG']['useSMTP'] = true;

        foreach (array('smtpHost', 'smtpUser', 'smtpPass', 'smtpEnc', 'smtpPort') as $strKey) {
            $this->arrSMTPCache[$strKey] = $GLOBALS['TL_CONFIG'][$strKey];
            $strEmailKey = 'email_' . $strKey;
            $GLOBALS['TL_CONFIG'][$strKey] = $this->objModel->{$strEmailKey};
        }
    }

    /**
     * Reset SMTP settings
     */
    protected function resetSMTPSettings()
    {
        if (!$this->objModel->email_overrideSmtp) {
            return;
        }

        foreach (array('useSMTP', 'smtpHost', 'smtpUser', 'smtpPass', 'smtpEnc', 'smtpPort') as $strKey) {
            $GLOBALS['TL_CONFIG'][$strKey] = $this->arrSMTPCache[$strKey];
        }
    }
}
