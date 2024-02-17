<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Language;

class tl_nc_language extends Backend
{

    /**
     * Modifies the palette for the queue gateway so it takes the one from the target gateway
     *
     * @param DataContainer $dc
     */
    public function modifyPalette(DataContainer $dc)
    {
        if ('edit' !== Input::get('act')) {
            return;
        }

        $language = Language::findByPk($dc->id);
        $message = $language->getRelated('pid');
        $gateway = $message->getRelated('gateway');

        if ($gateway !== null && 'queue' === $gateway->type) {
            $targetGateway = Gateway::findByPk($gateway->queue_targetGateway);
            $GLOBALS['TL_DCA']['tl_nc_language']['palettes']['queue'] =
                $GLOBALS['TL_DCA']['tl_nc_language']['palettes'][$targetGateway->type];
        }
    }

    /**
     * Save gateway type in language when creating new record
     *
     * @param string         $strTable
     * @param int            $insertID
     * @param array          $arrSet
     * @param DataContainer $dc
     */
    public function insertGatewayType($strTable, $insertID, $arrSet, $dc)
    {
        if ('tl_nc_language' === $strTable) {
            Database::getInstance()->prepare("
                UPDATE tl_nc_language SET gateway_type=(SELECT type FROM tl_nc_gateway WHERE id=(SELECT gateway FROM tl_nc_message WHERE id=?)) WHERE id=?
            ")->execute($arrSet['pid'], $insertID);
        }
    }


    /**
     * Check if the language field is unique per message
     *
     * @param mixed          $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateLanguageField($varValue, DataContainer $dc)
    {
        $objLanguages = $this->Database->prepare("SELECT id FROM tl_nc_language WHERE language=? AND pid=? AND id!=?")
            ->limit(1)
            ->execute($varValue, $dc->activeRecord->pid, $dc->id);

        if ($objLanguages->numRows)
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $dc->field));
        }

        return $varValue;
    }


    /**
     * Make sure the fallback field is a fallback per message
     *
     * @param mixed          $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateFallbackField($varValue, DataContainer $dc)
    {
        if ($varValue) {
            $objLanguages = $this->Database->prepare("SELECT id FROM tl_nc_language WHERE fallback=1 AND pid=? AND id!=?")
                ->limit(1)
                ->execute($dc->activeRecord->pid, $dc->id);

            if ($objLanguages->numRows) {
                $this->Database->prepare("UPDATE tl_nc_language SET fallback='' WHERE id=?")
                    ->execute($objLanguages->id);
            }
        }

        return $varValue;
    }


    /**
     * Validate e-mail addresses in the comma separated list
     *
     * @param mixed          $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateEmailList($varValue, DataContainer $dc)
    {
        if ($varValue != '') {
            $chunks = StringUtil::trimsplit(',', $varValue);

            foreach ($chunks as $chunk) {

                // Skip string with tokens or inserttags
                if (strpos($chunk, '##') !== false || strpos($chunk, '{{') !== false|| strpos($chunk, '{if') !== false) {
                    continue;
                }

                if (!Validator::isEmail($chunk)) {
                    throw new \Exception($GLOBALS['TL_LANG']['ERR']['emails']);
                }
            }
        }

        return $varValue;
    }
}
