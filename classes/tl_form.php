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

        $objNotification->send(
            $this->generateTokens(
                (array) $arrData,
                (array) $arrForm,
                (array) $arrFiles,
                (array) $arrLabels,
                $objNotification->flatten_delimiter ?: ','
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
     * @param string $delimiter
     *
     * @return array
     */
    public function generateTokens(array $arrData, array $arrForm, array $arrFiles, array $arrLabels, $delimiter)
    {
        $arrTokens = array();
        $arrTokens['raw_data'] = '';
        $arrTokens['raw_data_filled'] = '';

        // Data to use in message
        foreach ($arrData as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'form_'.$k, $arrTokens, $delimiter);
            $arrTokens['formlabel_'.$k] = isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k);
            $arrTokens['raw_data'] .= (isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
            if (is_array($v) || strlen($v)) {
                $arrTokens['raw_data_filled'] .= (isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
            }
        }

        // Add formconfig
        foreach ($arrForm as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'formconfig_'.$k, $arrTokens, $delimiter);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Delimiter
        $arrTokens['delimiter'] = $delimiter;

        // Upload fields
        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_' . $fieldName] = Form::getFileUploadPathForToken($file);
            $arrTokens['template_data']['file'][$fieldName] = Form::getFileUploadPathForToken($file);
        }

        // Data to use in template
        $fields = [];
        foreach (\FormFieldModel::findByPid($arrForm['id']) as $field) {
            $fields[$field->name] = $field;
        }

        foreach ($arrData as $k => $v) {
            $arrTokens['template_data']['name'][]      = $k;
            $arrTokens['template_data']['value'][$k]   = $v;
            $arrTokens['template_data']['label'][$k]   = isset($arrLabels[$k]) ? $arrLabels[$k] : $k;
            $arrTokens['template_data']['type'][$k]    = $fields[$k]->type;
            $arrTokens['template_data']['options'][$k] = null;
            if (null !== $fields[$k]->options) {
                foreach (StringUtil::deserialize($fields[$k]->options, true) as $option) {
                    if (is_array($v) && in_array($option['value'], $v)) {
                        $arrTokens['template_data']['options'][$k][$option['value']] = $option['label'];
                    }
                    if (!is_array($v) && $option['value'] == $v) {
                        $arrTokens['template_data']['options'][$k] = $option['label'];
                    }
                }
            }
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
