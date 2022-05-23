<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use Contao\StringUtil;
use NotificationCenter\Model\Notification as NotificationModel;


class AutoSuggester extends \Controller
{
    protected static $strTable;
    protected static $objNotification;
    protected static $strType;

    public static function load($dc)
    {
        // Already initialized (e.g. for another DCA)
        // This could be if Contao loads a ptable or ctable
        if (null !== static::$strTable) {
            return;
        }

        // @todo implement editAll and overrideAll
        if ('edit' !== \Input::get('act')) {
            return;
        }

        // @todo rename to nc_tokens
        \System::loadLanguageFile('tokens');
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/notification_center/assets/autosuggester' . ($GLOBALS['TL_CONFIG']['debugMode'] ? '' : '.min') . '.js';
        $GLOBALS['TL_CSS'][]        = 'system/modules/notification_center/assets/autosuggester' . ($GLOBALS['TL_CONFIG']['debugMode'] ? '' : '.min') . '.css';

        static::$strTable = $dc->table;
        static::$objNotification = \Database::getInstance()->prepare("SELECT * FROM tl_nc_notification WHERE id=(SELECT pid FROM tl_nc_message WHERE id=(SELECT pid FROM tl_nc_language WHERE id=?))")->execute($dc->id);
        static::$strType = static::$objNotification->type;

        foreach ($GLOBALS['TL_DCA'][static::$strTable]['fields'] as $field => $arrConfig) {
            if ('nc_tokens' === ($arrConfig['eval']['rgxp'] ?? null)) {
                $GLOBALS['TL_DCA'][static::$strTable]['fields'][$field]['wizard'][] = array('NotificationCenter\AutoSuggester', 'init');
            }
        }
    }


    /**
     * Initialize the auto suggester
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function init(\DataContainer $dc)
    {
        $strGroup  = NotificationModel::findGroupForType(static::$strType);
        $arrTokens = $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strGroup][static::$strType][$dc->field];
        $arrTokens = array_merge((array) $arrTokens, $this->loadTemplateTokens());

        if (!is_array($arrTokens) || empty($arrTokens)) {
            return '';
        }

        $arrParsedTokens = array();

        foreach ($arrTokens as $strToken) {
            $content = $GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][static::$strType][$strToken] ?? '';

            if (0 === strpos($strToken, 'template_')) {
                $content = sprintf($GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['template'], 'notification_'.substr($strToken, 9));
            }

            $arrParsedTokens[] = [
                'value'   => '##' . $strToken . '##',
                'content' => $content,
            ];
        }

        $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function() {
	new AutoSuggester($('ctrl_" . $dc->field . "'), " . json_encode($arrParsedTokens) . ");
});
</script>";

        return '';
    }


    /**
     * Verify tokens
     *
     * @param string  $rgxp
     * @param string  $strText
     * @param \Widget $objWidget
     *
     * @return bool
     */
    public function verifyTokens($rgxp, $strText, $objWidget)
    {
        if ('nc_tokens' !== $rgxp) {
            return false;
        }

        $strGroup = NotificationModel::findGroupForType(static::$strType);

        $validTokens = (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strGroup][static::$strType][$objWidget->name];
        $validTokens = array_merge($validTokens, $this->loadTemplateTokens());

        $strText = StringUtil::decodeEntities(StringUtil::restoreBasicEntities($strText));

        if (!$this->verifyHashes($strText, $objWidget, $validTokens)) {
            $this->verifyConditions($strText, $objWidget, $validTokens);
        }

        return true;
    }

    /**
     * @param string  $strText
     * @param \Widget $objWidget
     * @param array   $validTokens
     *
     * @return bool
     */
    private function verifyHashes($strText, $objWidget, array $validTokens)
    {
        // Check if tokens contain invalid characters
        preg_match_all('@##(.+?)##@', $strText, $matches);

        if (!is_array($matches) || empty($matches) || empty($matches[1])) {
            return false;
        }

        foreach ($matches[1] as $match) {
            if (preg_match('/[<>!=*]+/', $match)) {
                $objWidget->addError($GLOBALS['TL_LANG']['tl_nc_language']['token_character_error']);

                return true;
            }
        }

        // Build regex pattern
        $strPattern = '/##(' . implode('|', $validTokens) . ')##/i';
        $strPattern = str_replace('*', '[^##]*', $strPattern);

        preg_match_all($strPattern, $strText, $arrValidMatches);
        preg_match_all('/##([A-Za-z0-9_]+)##/i', $strText, $arrAllMatches);

        $arrInvalidTokens = array_diff($arrAllMatches[1], $arrValidMatches[1]);

        if (count($arrInvalidTokens)) {
            $strInvalidTokens = '##' . implode('##, ##', $arrInvalidTokens) . '##';
            $objWidget->addError(sprintf($GLOBALS['TL_LANG']['tl_nc_language']['token_error'], $strInvalidTokens));

            return true;
        }

        return false;
    }

    /**
     * @param string  $strText
     * @param \Widget $objWidget
     * @param array   $validTokens
     *
     * @return bool
     */
    private function verifyConditions($strText, $objWidget, array $validTokens)
    {
        // Need to collect
        if (!preg_match_all('/{(else)?if ([^=!<>\s]+)/i', $strText, $matches)) {
            return false;
        }

        $foundTokens = $matches[2];
        $invalidTokens = [];
        foreach (array_diff($foundTokens, $validTokens) as $found) {
            $invalid = true;

            foreach ($validTokens as $valid) {
                if ('*' === substr($valid, -1) && 0 === strpos($found, substr($valid, 0, -1))) {
                    $invalid = false;
                    break;
                }
            }

            if ($invalid) {
                $invalidTokens[] = $found;
            }
        }

        if (count($invalidTokens) > 0) {
            $objWidget->addError(sprintf($GLOBALS['TL_LANG']['tl_nc_language']['token_error'], implode(', ', $invalidTokens)));

            return true;
        }

        try {
            \StringUtil::parseSimpleTokens($strText, array_flip($foundTokens));
        } catch (\Exception $e) {
            $objWidget->addError($e->getMessage());

            return true;
        }

        return false;
    }

    private function loadTemplateTokens()
    {
        if (!static::$objNotification) {
            return [];
        }

        $tokens = [];
        $templates = deserialize(static::$objNotification->templates, true);

        foreach ($templates as $template) {
            $tokens[] = 'template_'.substr($template, 13);
        }

        return $tokens;
    }
}
