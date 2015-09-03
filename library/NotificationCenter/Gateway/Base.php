<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Gateway;
use NotificationCenter\Util\String;

/**
 * No need no extend Controller but left here for BC
 */
abstract class Base extends \Controller
{
    /**
     * Text filter options
     * @deprecated Use the Util\String constants instead (only here for BC)
     */
    const NO_TAGS = 1;
    const NO_BREAKS = 2;
    const NO_EMAILS = 4;

    /**
     * The gateway model
     * @var Gateway
     */
    protected $objModel = null;

    /**
     * Set notification type and models
     * @param   Notification
     * @param   Gateway
     */
    public function __construct(Gateway $objModel)
    {
        $this->objModel = $objModel;
    }

    /**
     * Gets the gateway model
     * @return  \NotificationCenter\Model\Gateway
     */
    public function getModel()
    {
        return $this->objModel;
    }

    /**
     * @deprecated Use String::getTokenAttachments()
     */
    protected function getTokenAttachments($strAttachmentTokens, array $arrTokens)
    {
        return String::getTokenAttachments($strAttachmentTokens, $arrTokens);
    }

    /**
     * @deprecated Use String::compileRecipients()
     */
    protected function compileRecipients($strRecipients, $arrTokens)
    {
        return String::compileRecipients($strRecipients, $arrTokens);
    }

    /**
     * @deprecated Use String::recursiveReplaceTokensAndTags()
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags = 0)
    {
        return String::recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags);
    }

    /**
     * @deprecated Use String::convertToText()
     */
    protected function convertToText($varValue, $options)
    {
        return String::convertToText($varValue, $options);
    }
}
