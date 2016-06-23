<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use NotificationCenter\Util\Form;

class tl_form extends \Backend
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get notification choices
     *
     * @return array
     */
    public function getNotificationChoices()
    {
        $arrChoices = array();
        $objNotifications = \Database::getInstance()->execute("SELECT id,title FROM tl_nc_notification WHERE type='core_form' ORDER BY title");

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }

    /**
     * Send the form notification
     *
     * @param array $arrData
     * @param array $arrForm
     * @param array $arrFiles
     * @param array $arrLabels
     */
    public function sendFormNotification($arrData, $arrForm, $arrFiles, $arrLabels)
    {
        if (!$arrForm['nc_notification'] || ($objNotification = Model\Notification::findByPk($arrForm['nc_notification'])) === null) {
            return;
        }

        /** @var Model\Notification $objNotification */
        $objNotification->send(
            $this->generateTokens(
                (array) $arrData,
                (array) $arrForm,
                (array) $arrFiles,
                (array) $arrLabels
            ),
            $GLOBALS['TL_LANGUAGE']
        );
    }

    /**
     * Generate the tokens
     *
     * @param array $arrData
     * @param array $arrForm
     * @param array $arrFiles
     * @param array $arrLabels
     *
     * @return array
     */
    public function generateTokens(array $arrData, array $arrForm, array $arrFiles, array $arrLabels)
    {
        $arrTokens = array();
        $arrTokens['raw_data'] = '';

        foreach ($arrData as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'form_'.$k, $arrTokens);
            $arrTokens['form_label_'.$k] = isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k);
            $arrTokens['raw_data'] .= (isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
        }

        foreach ($arrForm as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'formconfig_'.$k, $arrTokens);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Upload fields
        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_' . $fieldName] = Form::getFileUploadPathForToken($file);
        }

        return $arrTokens;
    }

    /**
     * Flatten input data, Simple Tokens can't handle arrays
     *
     * @param mixed  $varValue
     * @param string $strKey
     * @param array  $arrData
     *
     * @deprecated Deprecated since version 1.3.1, to be removed in version 2.
     *             Use Haste\Util\StringUtil::flatten() instead.
     */
    public function flatten($varValue, $strKey, &$arrData)
    {
        if (is_object($varValue)) {
            return;
        } elseif (!is_array($varValue)) {
            $arrData[$strKey] = $varValue;
            return;
        }

        $blnAssoc = array_is_assoc($varValue);
        $arrValues = array();

        foreach ($varValue as $k => $v) {
            if ($blnAssoc && !is_array($v)) {
                $this->flatten($v, $strKey.'_'.$k, $arrData);
            } else {
                $arrData[$strKey.'_'.$v] = '1';
                $arrValues[]             = $v;
            }
        }

        $arrData[$strKey] = implode(', ', $arrValues);
    }
}
