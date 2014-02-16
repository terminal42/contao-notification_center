<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  terminal42 gmbh 2013
 * @license    LGPL
 */

namespace NotificationCenter;

class tl_nc_gateway extends \Backend
{
    /**
     * Load FTP fields
     * @param \DataContainer
     */
    public function loadFtpFields(\DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        $objGateway = $this->Database->prepare("SELECT * FROM tl_nc_gateway WHERE id=?")
                                     ->limit(1)
                                     ->execute($dc->id);

        if ($objGateway->ftp_type == 'local') {
            unset($GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['ftp_host']);
            unset($GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['ftp_port']);
            unset($GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['ftp_username']);
            unset($GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['ftp_password']);
        }
    }

    /**
     * Check the FTP connection
     * @param \DataContainer
     */
    public function checkFileServerConnection(\DataContainer $dc)
    {
        if ($dc->activeRecord->type != 'ftp' || $dc->activeRecord->ftp_type == 'local') {
            return;
        }

        $strClass = $GLOBALS['NOTIFICATION_CENTER']['FTP'][$dc->activeRecord->ftp_type];

        if (!class_exists($strClass)) {
            \Message::addError($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_class']);
            return;
        }

        $objHandler = new $strClass();

        try {
            $objHandler->connect($dc->activeRecord);
        } catch (\Exception $e) {
            \Message::addError(sprintf($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_connect'], $e->getMessage()));
            return;
        }

        \Message::addConfirmation($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_confirm']);
    }
}