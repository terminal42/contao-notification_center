<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

class Message extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_message';


    /**
     * Send this message using its gateway
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(array $arrTokens, $strLanguage = '')
    {
        /** @var Gateway $objGatewayModel */
        if (($objGatewayModel = $this->getRelated('gateway')) === null) {
            \System::log(sprintf('Could not find gateway ID "%s".', $this->gateway), __METHOD__, TL_ERROR);

            return false;
        }

        if (null === $objGatewayModel->getGateway()) {
            \System::log(sprintf('Could not find gateway class for gateway ID "%s".', $objGatewayModel->id), __METHOD__, TL_ERROR);

            return false;
        }

        if (isset($GLOBALS['TL_HOOKS']['mapNotificationTokens']) && is_array($GLOBALS['TL_HOOKS']['mapNotificationTokens'])) {
            foreach ($GLOBALS['TL_HOOKS']['mapNotificationTokens'] as $arrCallback) {
                $arrTokens = \System::importStatic($arrCallback[0])->{$arrCallback[1]}($this, $arrTokens, $strLanguage, $objGatewayModel);
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['sendNotificationMessage']) && is_array($GLOBALS['TL_HOOKS']['sendNotificationMessage'])) {
            foreach ($GLOBALS['TL_HOOKS']['sendNotificationMessage'] as $arrCallback) {
                $blnSuccess = \System::importStatic($arrCallback[0])->{$arrCallback[1]}($this, $arrTokens, $strLanguage, $objGatewayModel);

                if (!$blnSuccess) {
                    return false;
                }
            }
        }

        return $objGatewayModel->getGateway()->send($this, $arrTokens, $strLanguage);
    }

    /**
     * Find all published by notification
     * @param   Notification
     * @return  Message|null
     */
    public static function findPublishedByNotification(Notification $objNotification, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t.pid=? AND $t.published=1");
        $arrValues  = array($objNotification->id);

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }
}
