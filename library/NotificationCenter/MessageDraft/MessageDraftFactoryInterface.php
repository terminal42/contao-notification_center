<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\MessageDraft;

use NotificationCenter\Model\Message;

interface MessageDraftFactoryInterface
{
    /**
     * Creates a MessageDraft
     * @param   Message
     * @param   array
     * @param   string
     * @return  MessageDraftInterface|null (if no draft could be found)
     */
    public function createDraft(Message $objMessage, array $arrTokens, $strLanguage = '');
}
