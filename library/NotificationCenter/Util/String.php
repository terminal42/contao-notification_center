<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Util;


use Haste\Haste;

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
     *
     * @param string $strText
     * @param array  $arrTokens    Array of Tokens
     * @param int    $intTextFlags Filters the tokens and the text for a given set of options
     *
     * @return string
     *
     * @deprecated Deprecated since version 1.3.1, to be removed in version 2.
     *             Use Haste\Util\StringUtil::recursiveReplaceTokensAndTags() instead.
     */
    public static function recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags = 0)
    {
        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strText, $arrTokens, $intTextFlags);
    }

    /**
     * Convert the given array or string to plain text using given options
     *
     * @deprecated Deprecated since version 1.3.1, to be removed in version 2.
     *             Use Haste\Util\StringUtil::convertToText() instead.
     *
     * @param mixed $varValue
     * @param int   $options
     *
     * @return mixed
     */
    public static function convertToText($varValue, $options)
    {
        return \Haste\Util\StringUtil::convertToText($varValue, $options);
    }

    /**
     * Gets an array of valid attachments of a token field
     *
     * @param string $strAttachmentTokens
     * @param array  $arrTokens
     *
     * @return array
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
     *
     * @param string $strRecipients
     * @param array  $arrTokens
     *
     * @return array
     */
    public static function compileRecipients($strRecipients, $arrTokens)
    {
        // Replaces tokens first so that tokens can contain a list of recipients.
        $strRecipients = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($strRecipients, $arrTokens, static::NO_TAGS | static::NO_BREAKS);
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
