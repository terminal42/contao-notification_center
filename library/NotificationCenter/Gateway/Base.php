<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Gateway;

use Codefog\HasteBundle\StringParser;
use Contao\Controller;
use Contao\System;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Util\StringUtil;

/**
 * No need no extend Controller but left here for BC
 */
abstract class Base extends Controller
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
     * @param   Gateway $objModel
     */
    public function __construct(Gateway $objModel)
    {
        $this->objModel = $objModel;
    }

    /**
     * Gets the gateway model
     * @return  Gateway
     */
    public function getModel()
    {
        return $this->objModel;
    }

    /**
     * @deprecated Use StringUtil::getTokenAttachments()
     */
    protected function getTokenAttachments($strAttachmentTokens, array $arrTokens)
    {
        return StringUtil::getTokenAttachments($strAttachmentTokens, $arrTokens);
    }

    /**
     * @deprecated Use StringUtil::compileRecipients()
     */
    protected function compileRecipients($strRecipients, $arrTokens)
    {
        return StringUtil::compileRecipients($strRecipients, $arrTokens);
    }

    /**
     * @deprecated Use \Haste\Util\StringUtil::recursiveReplaceTokensAndTags()
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags = 0)
    {
        return System::getContainer()->get(StringParser::class)->recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags);
    }

    /**
     * @deprecated Use \Haste\Util\StringUtil::convertToText()
     */
    protected function convertToText($varValue, $options)
    {
        return System::getContainer()->get(StringParser::class)->convertToText($varValue, $options);
    }
}
