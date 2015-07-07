<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2014
 * @author     Kamil Kuzminski <kamil.kuzminski@gmail.com>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
     * @param integer
     * @param array
     * @param object
     */
    public function sendRegistrationEmail($intId, $arrData, &$objModule)
    {
        if (!$objModule->nc_notification) {
            return;
        }

        $arrTokens                = array();
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrTokens['domain']      = \Environment::get('host');
        $arrTokens['link']        = \Environment::get('base') . \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $arrData['activation'];

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
     * @param object
     * @param array
     * @param object
     */
    public function sendPersonalDataEmail($objUser, $arrData, $objModule)
    {
        if (!$objModule->nc_notification) {
            return;
        }

        $arrTokens                = array();
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrTokens['domain']      = \Environment::get('host');

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
        }

        $objNotification = \NotificationCenter\Model\Notification::findByPk($objModule->nc_notification);

        if ($objNotification !== null) {
            $objNotification->send($arrTokens);
        }
    }

    /**
     * Remove Queue from back end navigation if no queue gateway is available yet
     *
     * @param array
     * @param bool
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
