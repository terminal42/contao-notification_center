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
    public static function getTokenAttachments($strAttachmentTokens, array $arrTokens)
    {
        return String::getTokenAttachments($strAttachmentTokens, $arrTokens);
    }

    /**
     * @deprecated Use String::compileRecipients()
     */
    public static function compileRecipients($strRecipients, $arrTokens)
    {
        return String::compileRecipients($strRecipients, $arrTokens);
    }

    /**
     * @deprecated Use String::recursiveReplaceTokensAndTags()
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags=0)
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
