<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\MessageDraft;

interface MessageDraftInterface
{
    /**
     * Returns the tokens for that message draft
     * @return array
     */
    public function getTokens();

    /**
     * Returns the message model for that message draft
     * @return \NotificationCenter\Model\Message
     */
    public function getMessage();

    /**
     * Returns the language for that message draft
     * @return string
     */
    public function getLanguage();
}
