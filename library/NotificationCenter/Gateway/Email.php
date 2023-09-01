<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
     *
     * @param Message $objMessage
     * @param array   $arrTokens
     * @param string  $strLanguage
     *
     * @return  EmailMessageDraft|null (if no draft could be found)
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

    private function instantiateEmail()
    {
        if (version_compare(VERSION, '4.10', '>=')) {
            $objEmail = new \Email();
            if ($this->objModel->mailerTransport) {
                $objEmail->addHeader('X-Transport', $this->objModel->mailerTransport);
            }

            return $objEmail;
        }

        // Override SMTP settings if desired
        if ($this->objModel->email_overrideSmtp) {
            if (method_exists(\Swift_SmtpTransport::class, 'newInstance')) {
                $transport = \Swift_SmtpTransport::newInstance($this->objModel->email_smtpHost, $this->objModel->email_smtpPort);
            } else {
                $transport = new \Swift_SmtpTransport($this->objModel->email_smtpHost, $this->objModel->email_smtpPort);
            }

            // Encryption
            if ($this->objModel->email_smtpEnc === 'ssl' || $this->objModel->email_smtpEnc === 'tls') {
                $transport->setEncryption($this->objModel->email_smtpEnc);
            }

            // Authentication
            if ($this->objModel->email_smtpUser) {
                $transport->setUsername($this->objModel->email_smtpUser)->setPassword($this->objModel->email_smtpPass);
            }

            return new \Email(new \Swift_Mailer($transport));
        }

        $objEmail = new \Email();
        $this->resetSMTPSettings();

        return $objEmail;
    }

    /**
     * @param EmailMessageDraft $objDraft
     */
    public function sendDraft(EmailMessageDraft $objDraft)
    {
        $objEmail = $this->instantiateEmail();

        // Set priority
        $objEmail->priority = $objDraft->getPriority();

        // Set optional sender name
        if ($strSenderName = $objDraft->getSenderName()) {
            $objEmail->fromName = $strSenderName;
        }

        // Set email sender address
        $objEmail->from = $objDraft->getSenderEmail();

        // Set reply-to address
        if ($strReplyTo = $objDraft->getReplyToEmail()) {
            try {
                $objEmail->replyTo($strReplyTo);
            } catch (\Exception $e) {
                \System::log(sprintf('Could not set reply-to address "%s" for message ID %s: %s', $strReplyTo, $objDraft->getMessage()->id, $e->getMessage()), __METHOD__, TL_ERROR);
                return false;
            }
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

        // Set image embedding
        $objEmail->embedImages = !$objDraft->useExternalImages();

        // Add file attachments
        $arrAttachments = $objDraft->getAttachments();
        if (!empty($arrAttachments)) {
            foreach ($arrAttachments as $strFile) {
                $objEmail->attachFile($strFile);
            }
        }

        // Add string attachments
        $arrAttachments = $objDraft->getStringAttachments();
        if (!empty($arrAttachments)) {
            foreach ($arrAttachments as $strFilename => $strContent) {
                $objEmail->attachFileFromString($strContent, $strFilename);
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

        if (empty($recipientEmails = $objDraft->getRecipientEmails())) {
            \System::log(sprintf('Recipient email is empty - could not send email to for message ID %s.', $objDraft->getMessage()->id), __METHOD__, TL_ERROR);
        }

        try {
            return $objEmail->sendTo($recipientEmails);
        } catch (\Exception $e) {
            \System::log(sprintf('Could not send email to "%s" for message ID %s: %s', implode(', ', $objDraft->getRecipientEmails()), $objDraft->getMessage()->id, $e->getMessage()), __METHOD__, TL_ERROR);
        }

        return false;
    }

    /**
     * Send email message
     *
     * @param Message $objMessage
     * @param array   $arrTokens
     * @param string  $strLanguage
     *
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

            \System::log(sprintf('Could not create draft message for e-mail (Message ID: %s)', $objMessage->id), __METHOD__, TL_ERROR);

            return false;
        }

        return $this->sendDraft($objDraft);
    }

    /**
     * Override SMTP settings
     * @deprecated
     */
    protected function overrideSMTPSettings()
    {
        // Does nothing
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
