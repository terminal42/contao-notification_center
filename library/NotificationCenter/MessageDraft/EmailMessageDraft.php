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

namespace NotificationCenter\MessageDraft;


use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;
use NotificationCenter\Util\String;

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
        $this->arrTokens = $arrTokens;
        $this->objLanguage = $objLanguage;
        $this->objMessage = $objMessage;
    }

    /**
     * Returns the sender email as a string
     * @return  string
     */
    public function getSenderEmail()
    {
        $strSenderAddress = $this->objLanguage->email_sender_address ? : $GLOBALS['TL_ADMIN_EMAIL'];
        return String::recursiveReplaceTokensAndTags($strSenderAddress, $this->arrTokens, String::NO_TAGS|String::NO_BREAKS);
    }

    /**
     * Returns the sender name as a string
     * @return  string
     */
    public function getSenderName()
    {
        $strSenderName = $this->objLanguage->email_sender_name ? : $GLOBALS['TL_ADMIN_NAME'];
        return String::recursiveReplaceTokensAndTags($strSenderName, $this->arrTokens, String::NO_TAGS|String::NO_BREAKS);
    }

    /**
     * Returns the recipient emails
     * @return  array
     */
    public function getRecipientEmails()
    {
        return String::compileRecipients($this->objLanguage->recipients, $this->arrTokens);
    }

    /**
     * Returns the carbon copy recipient emails
     * @return  array
     */
    public function getCcRecipientEmails()
    {
        return String::compileRecipients($this->objLanguage->email_recipient_cc, $this->arrTokens);
    }

    /**
     * Returns the blind carbon copy recipient emails
     * @return  array
     */
    public function getBccRecipientEmails()
    {
        return String::compileRecipients($this->objLanguage->email_recipient_bcc, $this->arrTokens);
    }

    /**
     * Returns the replyTo email address
     * @return  string
     */
    public function getReplyToEmail()
    {
        if ($this->objLanguage->email_replyTo) {
            return String::recursiveReplaceTokensAndTags($this->objLanguage->email_replyTo, $this->arrTokens, String::NO_TAGS|String::NO_BREAKS);
        }

        return '';
    }

    /**
     * Returns the subject as a string
     * @return  string
     */
    public function getSubject()
    {
        return String::recursiveReplaceTokensAndTags($this->objLanguage->email_subject, $this->arrTokens, String::NO_TAGS|String::NO_BREAKS);
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
        $strText = String::recursiveReplaceTokensAndTags($strText, $this->arrTokens, String::NO_TAGS);
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
            $strHtml = String::recursiveReplaceTokensAndTags($strHtml, $this->arrTokens);
            $strHtml = \Controller::convertRelativeUrls($strHtml, '', true);
            $strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);
            return $strHtml;
        }

        return '';
    }

    /**
     * Returns the paths to attachments as an array
     * @return  array
     */
    public function getAttachments()
    {
        // Token attachments
        $arrAttachments = String::getTokenAttachments($this->objLanguage->attachment_tokens, $this->arrTokens);

        // Add static attachments
        $arrStaticAttachments = deserialize($this->objLanguage->attachments, true);

        if (!empty($arrStaticAttachments)) {
            $objFiles = \FilesModel::findMultipleByUuids($arrStaticAttachments);
            while ($objFiles->next()) {
                $arrAttachments[] = TL_ROOT . '/' . $objFiles->path;
            }
        }

        return $arrAttachments;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyValueArray()
    {
        return array(
            'sender_email'          => $this->getSenderName(),
            'sender_name'           => $this->getSenderName(),
            'recipient_emails'      => $this->getRecipientEmails(),
            'cc_recipient_emails'   => $this->getCcRecipientEmails(),
            'bcc_recipient_emails'  => $this->getBccRecipientEmails(),
            'replyto_email'         => $this->getReplyToEmail(),
            'subject'               => $this->getSubject(),
            'priority'              => $this->getPriority(),
            'text'                  => $this->getTextBody(),
            'html'                  => $this->getHtmlBody(),
            'attachments'           => $this->getAttachments()
        );
    }
}
