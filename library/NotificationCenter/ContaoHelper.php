<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;


class ContaoHelper extends \Controller
{
    /**
     * Public constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Send a registration e-mail
     *
     * @param int    $intId
     * @param array  $arrData
     * @param object $objModule
     */
    public function sendRegistrationEmail($intId, $arrData, $objModule)
    {
        if (!$objModule->nc_notification) {
            return;
        }

        $arrTokens = array(
            'admin_email' => $GLOBALS['TL_ADMIN_EMAIL'],
            'domain'      => \Environment::get('host'),
            'link'        => \Environment::get('base') . \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $arrData['activation']
        );

        // Support newsletters
        if (in_array('newsletter', \ModuleLoader::getActive())) {
            if (!is_array($arrData['newsletter'])) {
                if ($arrData['newsletter'] != '') {
                    $objChannels                    = \Database::getInstance()->execute("SELECT title FROM tl_newsletter_channel WHERE id IN(" . implode(',', array_map('intval', (array) $arrData['newsletter'])) . ")");
                    $arrTokens['member_newsletter'] = implode("\n", $objChannels->fetchEach('title'));
                } else {
                    $arrTokens['member_newsletter'] = '';
                }
            }
        }

        // translate/format values
        foreach ($arrData as $strFieldName => $strFieldValue) {
            $arrTokens['member_' . $strFieldName] = \Haste\Util\Format::dcaValue('tl_member', $strFieldName, $strFieldValue);
        }

        $objNotification = \NotificationCenter\Model\Notification::findByPk($objModule->nc_notification);

        if ($objNotification !== null) {
            $objNotification->send($arrTokens);

            // Disable the email to admin because no core notification has been sent
            $objModule->reg_activate = true;
        }
    }


    /**
     * Send the personal data change e-mail
     *
     * @param object $objUser
     * @param array  $arrData
     * @param object $objModule
     */
    public function sendPersonalDataEmail($objUser, $arrData, $objModule)
    {
        if (!$objModule->nc_notification) {
            return;
        }

        $arrTokens = array(
            'admin_email' => $GLOBALS['TL_ADMIN_EMAIL'],
            'domain'      => \Environment::get('host'),
        );

        // Support newsletters
        if (in_array('newsletter', $this->Config->getActiveModules())) {
            if (!is_array($arrData['newsletter'])) {
                if ($arrData['newsletter'] != '') {
                    $objChannels                    = \Database::getInstance()->execute("SELECT title FROM tl_newsletter_channel WHERE id IN(" . implode(',', array_map('intval', (array) $arrData['newsletter'])) . ")");
                    $arrTokens['member_newsletter'] = implode("\n", $objChannels->fetchEach('title'));
                } else {
                    $arrTokens['member_newsletter'] = '';
                }
            }
        }

        // Translate/format old values
        foreach ($_SESSION['PERSONAL_DATA'] as $strFieldName => $strFieldValue) {
            $arrTokens['member_old_' . $strFieldName] = \Haste\Util\Format::dcaValue('tl_member', $strFieldName, $strFieldValue);
        }

        // Translate/format new values
        foreach ($arrData as $strFieldName => $strFieldValue) {
            $arrTokens['member_' . $strFieldName] = \Haste\Util\Format::dcaValue('tl_member', $strFieldName, $strFieldValue);

            if ((string) $arrTokens['member_' . $strFieldName] !== (string) $arrTokens['member_old_' . $strFieldName]) {
                $arrTokens['changed_' . $strFieldName] = '1';
            } else {
                $arrTokens['changed_' . $strFieldName] = '0';
            }
        }

        $objNotification = \NotificationCenter\Model\Notification::findByPk($objModule->nc_notification);

        if ($objNotification !== null) {
            $objNotification->send($arrTokens);
        }
    }

    /**
     * Remove Queue from back end navigation if no queue gateway is available yet
     *
     * @param array $arrModules
     * @param bool  $blnShowAll
     *
     * @return array
     */
    public function addQueueToUserNavigation($arrModules, $blnShowAll)
    {
        // Make sure there's no exception if notification_center has not been properly installed yet
        if (!\Database::getInstance()->tableExists('tl_nc_gateway')) {
            return $arrModules;
        }

        if (!\Database::getInstance()
            ->prepare('SELECT COUNT(id) as count FROM tl_nc_gateway WHERE type=? AND tstamp>0')
            ->execute('queue')->count
        ) {
            unset($arrModules['notification_center']['modules']['nc_queue']);
        }

        return $arrModules;
    }
}
