<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

use NotificationCenter\Gateway\Email;

class Message extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_message';

    /**
     * @var string|null
     */
    private $currentLanguage = null;

    /**
     * @var string|null
     */
    private $currentLocale = null;


    /**
     * Send this message using its gateway
     *
     * @param array      $arrTokens
     * @param string     $strLanguage
     * @param array|null $arrAttachments
     *
     * @return  bool
     * @throws \Exception
     */
    public function send(array $arrTokens, $strLanguage = '', array $arrAttachments = [])
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

        // Make sure the tokens and language can be changed for one message only
        // (by reference affects subsequent messages)
        $cpTokens   = $arrTokens;
        $cpLanguage = $strLanguage;

        if (isset($GLOBALS['TL_HOOKS']['sendNotificationMessage']) && is_array($GLOBALS['TL_HOOKS']['sendNotificationMessage'])) {
            foreach ($GLOBALS['TL_HOOKS']['sendNotificationMessage'] as $arrCallback) {
                $blnSuccess = \System::importStatic($arrCallback[0])->{$arrCallback[1]}($this, $cpTokens, $cpLanguage, $objGatewayModel);

                if (!$blnSuccess) {
                    return false;
                }
            }
        }

        $cpLanguage = $cpLanguage ?: $GLOBALS['TL_LANGUAGE'];
        if (null !== ($objLanguage = Language::findByMessageAndLanguageOrFallback($this, $cpLanguage))) {
             // Switch to the language of the notification
            $this->saveCurrentFrameworkLanguage();
            $this->setFrameworkLanguage($objLanguage->language, str_replace('-', '_', $objLanguage->language));
        }

        $objGateway = $objGatewayModel->getGateway();

        // Send the draft with updated attachments (likely originating from queue)
        if ($objGateway instanceof Email && count($arrAttachments) > 0) {
            $objDraft = $objGateway->createDraft($this, $cpTokens, $cpLanguage);

            // return false if no language found for BC
            if ($objDraft === null) {
                \System::log(sprintf('Could not create draft message for e-mail (Message ID: %s)', $this->id), __METHOD__, TL_ERROR);

                return false;
            }

            $objDraft->setAttachments($arrAttachments);

            $return = $objGateway->sendDraft($objDraft);

            $this->setFrameworkLanguage($this->currentLanguage, $this->currentLocale);

            return $return;
        }

        $return = $objGateway->send($this, $cpTokens, $cpLanguage);

        $this->setFrameworkLanguage($this->currentLanguage, $this->currentLocale);

        return $return;
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

    /**
     * Checks whether this message has a given token.
     * @param string $token
     * @return bool
     */
    public function hasToken($token)
    {
        $language = Language::findByMessageAndLanguageOrFallback($this, $GLOBALS['TL_LANGUAGE']);

        if (null === $language) {
            return false;
        }

        foreach ($language->row() as $value) {
            if (false !== strpos($value, '##'.$token.'##')) {
                return true;
            }
        }

        return false;
    }

    private function saveCurrentFrameworkLanguage()
    {
        $this->currentLocale = \Contao\System::getContainer()->get('translator')->getLocale();
    }

    /**
     * @param string $language
     */
    private function setFrameworkLanguage($language, $locale)
    {
        if (!$language || !$locale) {
            return;
        }

        $GLOBALS['TL_LANGUAGE'] = $language;
        \Contao\System::getContainer()->get('translator')->setLocale($locale);
    }
}
