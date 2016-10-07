<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\MessageDraft;


use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;
use NotificationCenter\Util\StringUtil;

class EmailMessageDraft implements MessageDraftInterface
{
    /**
     * Message
     * @var Message
     */
    protected $objMessage = null;

    /**
     * Language
     * @var Language
     */
    protected $objLanguage = null;

    /**
     * Tokens
     * @var array
     */
    protected $arrTokens = array();

    /**
     * Construct the object
     * @param Message  $objMessage
     * @param Language $objLanguage
     * @param          $arrTokens
     */
    public function __construct(Message $objMessage, Language $objLanguage, $arrTokens)
    {
        $this->arrTokens   = $arrTokens;
        $this->objLanguage = $objLanguage;
        $this->objMessage  = $objMessage;
    }

    /**
     * Returns the sender email as a string
     * @return  string
     */
    public function getSenderEmail()
    {
        $strSenderAddress = $this->objLanguage->email_sender_address ?: $GLOBALS['TL_ADMIN_EMAIL'];

        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strSenderAddress, $this->arrTokens, StringUtil::NO_TAGS | StringUtil::NO_BREAKS);
    }

    /**
     * Returns the sender name as a string
     * @return  string
     */
    public function getSenderName()
    {
        $strSenderName = $this->objLanguage->email_sender_name ?: $GLOBALS['TL_ADMIN_NAME'];

        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strSenderName, $this->arrTokens, StringUtil::NO_TAGS | StringUtil::NO_BREAKS);
    }

    /**
     * Returns the recipient emails
     * @return  array
     */
    public function getRecipientEmails()
    {
        return StringUtil::compileRecipients($this->objLanguage->recipients, $this->arrTokens);
    }

    /**
     * Returns the carbon copy recipient emails
     * @return  array
     */
    public function getCcRecipientEmails()
    {
        return StringUtil::compileRecipients($this->objLanguage->email_recipient_cc, $this->arrTokens);
    }

    /**
     * Returns the blind carbon copy recipient emails
     * @return  array
     */
    public function getBccRecipientEmails()
    {
        return StringUtil::compileRecipients($this->objLanguage->email_recipient_bcc, $this->arrTokens);
    }

    /**
     * Returns the replyTo email address
     * @return  string
     */
    public function getReplyToEmail()
    {
        if ($this->objLanguage->email_replyTo) {
            return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($this->objLanguage->email_replyTo, $this->arrTokens, StringUtil::NO_TAGS | StringUtil::NO_BREAKS);
        }

        return '';
    }

    /**
     * Returns the subject as a string
     * @return  string
     */
    public function getSubject()
    {
        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($this->objLanguage->email_subject, $this->arrTokens, StringUtil::NO_TAGS | StringUtil::NO_BREAKS);
    }

    /**
     * Returns the priority of the email
     * 1 = Highest
     * 2 = High
     * 3 = Normal
     * 4 = Low
     * 5 = Lowest
     * @return  int
     */
    public function getPriority()
    {
        return $this->objMessage->email_priority;
    }

    /**
     * Returns the text body as a string
     * @return  string
     */
    public function getTextBody()
    {
        $strText = $this->objLanguage->email_text;
        $strText = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strText, $this->arrTokens, StringUtil::NO_TAGS);

        return \Controller::convertRelativeUrls($strText, '', true);
    }

    /**
     * Returns the html body as a string
     * @return  string
     */
    public function getHtmlBody()
    {
        if ($this->objLanguage->email_mode == 'textAndHtml') {
            $objTemplate          = new \FrontendTemplate($this->objMessage->email_template);
            $objTemplate->body    = $this->objLanguage->email_html;
            $objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];

            // Prevent parseSimpleTokens from stripping important HTML tags
            $GLOBALS['TL_CONFIG']['allowedTags'] .= '<doctype><html><head><meta><style><body>';
            $strHtml = str_replace('<!DOCTYPE', '<DOCTYPE', $objTemplate->parse());
            $strHtml = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strHtml, $this->arrTokens);
            $strHtml = \Controller::convertRelativeUrls($strHtml, '', true);
            $strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);

            return $strHtml;
        }

        return '';
    }

    /**
     * Returns true if images should not be embedded
     * @return boolean
     */
    public function useExternalImages() {
        return (bool) $this->objLanguage->email_external_images;
    }

    /**
     * Returns the paths to attachments as an array
     * @return  array
     */
    public function getAttachments()
    {
        // Token attachments
        $arrAttachments = StringUtil::getTokenAttachments($this->objLanguage->attachment_tokens, $this->arrTokens);

        // Add static attachments
        $arrStaticAttachments = deserialize($this->objLanguage->attachments, true);

        if (!empty($arrStaticAttachments)) {
            $objFiles = \FilesModel::findMultipleByUuids($arrStaticAttachments);

            if ($objFiles === null) {
                return $arrAttachments;
            }

            while ($objFiles->next()) {
                $arrAttachments[] = TL_ROOT . '/' . $objFiles->path;
            }
        }

        return $arrAttachments;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        return $this->arrTokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->objMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->objLanguage->language;
    }
}
