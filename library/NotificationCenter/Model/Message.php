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
        if (($objGatewayModel = $this->getRelated('gateway')) === null) {
            \System::log(sprintf('Could not find gateway ID "%s".', $this->gateway), __METHOD__, TL_ERROR);

            return false;
        }

        if (null !== $objGatewayModel->getGateway()) {
            return $objGatewayModel->getGateway()->send($this, $arrTokens, $strLanguage);
        }

        \System::log(sprintf('Could not find gateway class for gateway ID "%s".', $objGatewayModel->id), __METHOD__, TL_ERROR);

        return false;
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
