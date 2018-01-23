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

            \System::log(sprintf('Could not create draft message for e-mail (Message ID: %s)', $objMessage->id), __METHOD__, TL_ERROR);

            return false;
        }

        // Override SMTP settings if desired
        if (version_compare(VERSION, '4.4', '>=') && $this->objModel->email_overrideSmtp) {
            if (version_compare(VERSION, '4.5', '>=')) {
                $transport = new \Swift_SmtpTransport($this->objModel->email_smtpHost, $this->objModel->email_smtpPort);
            } else {
                $transport = \Swift_SmtpTransport::newInstance($this->objModel->email_smtpHost, $this->objModel->email_smtpPort);
            }

            // Encryption
            if ($this->objModel->email_smtpEnc === 'ssl' || $this->objModel->email_smtpEnc === 'tls') {
                $transport->setEncryption($this->objModel->email_smtpEnc);
            }

            // Authentication
            if ($this->objModel->email_smtpUser) {
                $transport->setUsername($this->objModel->email_smtpUser)->setPassword($this->objModel->email_smtpPass);
            }

            $objEmail = new \Email(new \Swift_Mailer($transport));
        } else {
            $this->overrideSMTPSettings();
            $objEmail = new \Email();
            $this->resetSMTPSettings();
        }

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

        // Set image embedding
        $objEmail->embedImages = !$objDraft->useExternalImages();

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

        $this->arrSMTPCache['useSMTP']   = $GLOBALS['TL_CONFIG']['useSMTP'];
        $GLOBALS['TL_CONFIG']['useSMTP'] = true;

        foreach (array('smtpHost', 'smtpUser', 'smtpPass', 'smtpEnc', 'smtpPort') as $strKey) {
            $this->arrSMTPCache[$strKey]   = $GLOBALS['TL_CONFIG'][$strKey];
            $strEmailKey                   = 'email_' . $strKey;
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
