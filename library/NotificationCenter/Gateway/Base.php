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

abstract class Base extends \Controller
{

    /**
     * Text filter options
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
                $strAddress = $this->recursiveReplaceTokensAndTags($strAddress, $arrTokens, static::NO_TAGS|static::NO_BREAKS);

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
     * @param   int
     * @return  string
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags=0)
    {
        if ($intTextFlags > 0) {
            $arrTokens = $this->convertToText($arrTokens, $intTextFlags);
        }

        // Must decode, tokens could be encoded
        $strText = \String::decodeEntities($strText);

        // first parse the tokens as they might have if-else clauses
        $strBuffer = \String::parseSimpleTokens($strText, $arrTokens);

        // then replace the insert tags
        $strBuffer = $this->replaceInsertTags($strBuffer, false);

        // check if the inserttags have returned a simple token or an insert tag to parse
        if ((strpos($strBuffer, '##') !== false || strpos($strBuffer, '{{') !== false) && $strBuffer != $strText) {
            $strBuffer = $this->recursiveReplaceTokensAndTags($strBuffer, $arrTokens, $intTextFlags);
        }

        $strBuffer = \String::restoreBasicEntities($strBuffer);

        if ($intTextFlags > 0) {
            $strBuffer = $this->convertToText($strBuffer, $intTextFlags);
        }

        return $strBuffer;
    }

    /**
     * Convert the given array or string to plain text using given options
     * @param   mixed
     * @param   int
     * @return  mixed
     */
    protected function convertToText($varValue, $options)
    {
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = $this->convertToText($v, $options);
            }

            return $varValue;
        }

        // Replace friendly email before stripping tags
        if (!($options & static::NO_EMAILS)) {
            $arrEmails = array();
            preg_match_all('{<.+@.+\.[A-Za-z]{2,6}>}', $varValue, $arrEmails);

            if (!empty($arrEmails[0])) {
                foreach ($arrEmails[0] as $k => $v) {
                    $varValue = str_replace($v, '%email'.$k.'%', $varValue);
                }
            }
        }

        // Remove HTML tags but keep line breaks for <br> and <p>
		if ($options & static::NO_TAGS) {
            $varValue = strip_tags(preg_replace('{(?!^)<(br|p|/p).*?/?>\n?(?!$)}is', "\n", $varValue));
		}

        // Remove line breaks (e.g. for subject)
		if ($options & static::NO_BREAKS) {
    		$varValue = str_replace(array("\r", "\n"), '', $varValue);
		}

        // Restore friendly email after stripping tags
        if (!($options & static::NO_EMAILS) && !empty($arrEmails[0])) {
            foreach ($arrEmails[0] as $k => $v) {
                $varValue = str_replace('%email'.$k.'%', $v, $varValue);
            }
        }

        return $varValue;
    }
}
