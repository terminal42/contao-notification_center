<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use Codefog\HasteBundle\StringParser;
use Contao\ArrayUtil;
use Contao\System;
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
     * @param \Contao\Form $form
     */
    public function sendFormNotification($arrData, $arrForm, $arrFiles, $arrLabels, $form)
    {
        if (!$arrForm['nc_notification'] || ($objNotification = Model\Notification::findByPk($arrForm['nc_notification'])) === null) {
            return;
        }

        $objNotification->setForm($form);
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

        $stringParser = System::getContainer()->get(StringParser::class);

        foreach ($arrData as $k => $v) {
            $stringParser->flatten($v, 'form_'.$k, $arrTokens, $delimiter);
            $arrTokens['formlabel_'.$k] = $arrLabels[$k] ?? ucfirst($k);
            $arrTokens['raw_data'] .= ($arrLabels[$k] ?? ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
            if (is_array($v) || strlen($v)) {
                $arrTokens['raw_data_filled'] .= ($arrLabels[$k] ?? ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
            }
        }

        foreach ($arrForm as $k => $v) {
            $stringParser->flatten($v, 'formconfig_'.$k, $arrTokens, $delimiter);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Upload fields
        $arrFileNames = array();
        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_' . $fieldName] = Form::getFileUploadPathForToken($file);
            $arrFileNames[] = $file['name'];
        }
        $arrTokens['filenames'] = implode($delimiter, $arrFileNames);

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

        $blnAssoc = ArrayUtil::isAssoc($varValue);
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
