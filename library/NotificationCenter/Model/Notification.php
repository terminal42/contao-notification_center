<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

class Notification extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_notification';

    /**
     * Gets the published notifications collection
     * @return Message[]
     */
    public function getMessages()
    {
        return Message::findPublishedByNotification($this);
    }

    /**
     * Sends a notification
     * @param   array   The tokens
     * @param   string  The language (optional)
     * @return  array
     */
    public function send(array $arrTokens, $strLanguage = '')
    {
        // Check if there are valid messages
        if (($objMessages = $this->getMessages()) === null) {
            \System::log('Could not find any messages for notification ID ' . $this->id, __METHOD__, TL_ERROR);

            return array();
        }

        $arrResult = array();

        foreach ($objMessages as $objMessage) {
            $arrResult[$objMessage->id] = $objMessage->send($arrTokens, $strLanguage);
        }

        return $arrResult;
    }

    /**
     * Sends a personalized notification
     * @param   array   The tokens
     * @param   array   List of personalized tokens
     * @param   string  The language (optional)
     * @return  array
     */
    public function sendPersonalized(array $arrTokens, array $arrPersonalized, $strLanguage = '')
    {
        // Check if there are valid messages
        if (($objMessages = $this->getMessages()) === null) {
            \System::log('Could not find any messages for notification ID ' . $this->id, __METHOD__, TL_ERROR);

            return array();
        }

        $arrResult = array();

        foreach ($objMessages as $objMessage) {
            $arrResult[$objMessage->id] = $objMessage->sendPersonalized($arrTokens, $arrPersonalized, $strLanguage);
        }

        return $arrResult;
    }

    /**
     * Find notification group for type
     * @param   string Type
     * @return  string Class
     */
    public static function findGroupForType($strType)
    {
        foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $strGroup => $arrTypes) {
            if (in_array($strType, array_keys($arrTypes))) {
                return $strGroup;
            }
        }

        return '';
    }
}
