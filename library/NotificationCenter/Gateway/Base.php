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

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;

abstract class Base
{

    /**
     * The gateway model
     * @var Gateway
     */
    protected $objModel = null;

    /**
     * Set notification type and models
     * @param   Notification
     * @param   Gateway
     */
    public function __construct(Gateway $objModel)
    {
        $this->objModel = $objModel;
    }

    /**
     * Gets the gateway model
     * @return  NotificationCenter\Model\Gateway
     */
    public function getModel()
    {
        return $this->objModel;
    }

    /**
     * Gets an array of valid attachments
     * @param   string
     * @param   array Tokens
     * @return  array
     */
    protected function getAttachments($strAttachments, array $arrTokens)
    {
        $arrAttachments = array();

        if ($strAttachments == '') {
            return $arrAttachments;
        }

        foreach (trimsplit(',', $strAttachments) as $strToken) {
            $strFile = TL_ROOT . '/' . \String::parseSimpleTokens($strToken, $arrTokens);

            if (is_file($strFile)) {
                $arrAttachments[$strToken] = $strFile;
            }
        }

        return $arrAttachments;
    }
}
