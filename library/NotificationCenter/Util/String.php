<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Util;


class String
{
    /**
     * Text filter options
     */
    const NO_TAGS = 1;
    const NO_BREAKS = 2;
    const NO_EMAILS = 4;


    /**
     * Recursively replace simple tokens and insert tags
     * @param   string $strText
     * @param   array  $arrTokens Array of Tokens
     * @param   int    $intTextFlags Filters the tokens and the text for a given set of options
     *
     * @return  string
     */
    public static function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags = 0)
    {
        if ($intTextFlags > 0) {
            $arrTokens = static::convertToText($arrTokens, $intTextFlags);
        }

        // Must decode, tokens could be encoded
        $strText = \String::decodeEntities($strText);

        // Replace all opening and closing tags with a hash so they don't get stripped
        // by parseSimpleTokens() - this is useful e.g. for XML content
        $strHash                = md5($strText);
        $strTagOpenReplacement  = 'NC-TAG-OPEN-' . $strHash;
        $strTagCloseReplacement = 'NC-TAG-CLOSE-' . $strHash;
        $arrOriginal            = array('<', '>');
        $arrReplacement         = array($strTagOpenReplacement, $strTagCloseReplacement);

        $strText = str_replace($arrOriginal, $arrReplacement, $strText);

        // first parse the tokens as they might have if-else clauses
        $strBuffer = \String::parseSimpleTokens($strText, $arrTokens);

        $strBuffer = str_replace($arrReplacement, $arrOriginal, $strBuffer);

        // then replace the insert tags
        $strBuffer = \Haste\Haste::getInstance()->call('replaceInsertTags', array($strBuffer, false));

        // check if the inserttags have returned a simple token or an insert tag to parse
        if ((strpos($strBuffer, '##') !== false || strpos($strBuffer, '{{') !== false) && $strBuffer != $strText) {
            $strBuffer = static::recursiveReplaceTokensAndTags($strBuffer, $arrTokens, $intTextFlags);
        }

        $strBuffer = \String::restoreBasicEntities($strBuffer);

        if ($intTextFlags > 0) {
            $strBuffer = static::convertToText($strBuffer, $intTextFlags);
        }

        return $strBuffer;
    }

    /**
     * Convert the given array or string to plain text using given options
     * @param   mixed
     * @param   int
     * @return  mixed
     */
    public static function convertToText($varValue, $options)
    {
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = static::convertToText($v, $options);
            }

            return $varValue;
        }

        // Replace friendly email before stripping tags
        if (!($options & static::NO_EMAILS)) {
            $arrEmails = array();
            preg_match_all('{<.+@.+\.[A-Za-z]{2,6}>}', $varValue, $arrEmails);

            if (!empty($arrEmails[0])) {
                foreach ($arrEmails[0] as $k => $v) {
                    $varValue = str_replace($v, '%email' . $k . '%', $varValue);
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
                $varValue = str_replace('%email' . $k . '%', $v, $varValue);
            }
        }

        return $varValue;
    }

    /**
     * Gets an array of valid attachments of a token field
     * @param   string
     * @param   array Tokens
     * @return  array
     */
    public static function getTokenAttachments($strAttachmentTokens, array $arrTokens)
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
    public static function compileRecipients($strRecipients, $arrTokens)
    {
        // Replaces tokens first so that tokens can contain a list of recipients.
        $strRecipients = static::recursiveReplaceTokensAndTags($strRecipients, $arrTokens, static::NO_TAGS | static::NO_BREAKS);
        $arrRecipients = array();

        foreach ((array) trimsplit(',', $strRecipients) as $strAddress) {
            if ($strAddress != '') {
                list($strName, $strEmail) = \String::splitFriendlyEmail($strAddress);

                // Address could become empty through invalid insert tag
                if ($strAddress == '' || !\Validator::isEmail($strEmail)) {
                    continue;
                }

                $arrRecipients[] = $strAddress;
            }
        }

        return $arrRecipients;
    }
} 
