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
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;

abstract class Base extends \Controller
{

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
     * @return  NotificationCenter\Model\Gateway
     */
    public function getModel()
    {
        return $this->objModel;
    }

    /**
     * Gets an array of valid attachments of a token field
     * @param   string
     * @param   array Tokens
     * @return  array
     */
    protected function getTokenAttachments($strAttachmentTokens, array $arrTokens)
    {
        $arrAttachments = array();

        if ($strAttachmentTokens == '') {
            return $arrAttachments;
        }

        foreach (trimsplit(',', $strAttachmentTokens) as $strToken) {
            $strFile = TL_ROOT . '/' . \String::parseSimpleTokens($strToken, $arrTokens);

            if (is_file($strFile)) {
                $arrAttachments[$strToken] = $strFile;
            }
        }

        return $arrAttachments;
    }


    /**
     * Generate CC or BCC recipients from comma separated string
     * @param string
     */
    protected function compileRecipients($strRecipients, $arrTokens)
    {
        $arrRecipients = array();

        foreach ((array) trimsplit(',', $strRecipients) as $strAddress) {
            if ($strAddress != '') {
                $strAddress = $this->recursiveReplaceTokensAndTags($strAddress, $arrTokens);
                $strAddress = strip_tags($strAddress);

                // Address could become empty through invalid inserttag
                if ($strAddress == '' || !\Validator::isEmail($strAddress)) {
                    continue;
                }

                $arrRecipients[] = $strAddress;
            }
        }

        return $arrRecipients;
    }


    /**
     * Recursively replace simple tokens and insert tags
     * @param   string
     * @param   array tokens
     * @return  string
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens)
    {
        // Must decode, tokens could be encoded
        $strText = \String::decodeEntities($strText);

        // first parse the tokens as they might have if-else clauses
        $strBuffer = \String::parseSimpleTokens($strText, $arrTokens);

        // then replace the insert tags
        $strBuffer = $this->replaceInsertTags($strBuffer, false);

        // check if the inserttags have returned a simple token or an insert tag to parse
        if ((strpos($strBuffer, '##') !== false || strpos($strBuffer, '{{') !== false) && $strBuffer != $strText) {
            $strBuffer = $this->recursiveReplaceTokensAndTags($strBuffer, $arrTokens);
        }

        $strBuffer = \String::restoreBasicEntities($strBuffer);

        return $strBuffer;
    }
}
