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

namespace NotificationCenter\Model;

use NotificationCenter\BagType\BagTypeInterface;

class Notification extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_notification';

    /**
     * Constructs the gateway
     * @param   BagTypeInterface
     * @param   string Language
     * @return  GatewayInterface|null
     */
    public function buildGateway(BagTypeInterface $objBagType, $strLanguage)
    {
        if (($objGatewayModel = $this->getRelated('gateway')) === null) {
            \System::log(sprintf(
                'Could not find gateway ID "%s".',
                $this->gateway),
                __METHOD__,
                TL_ERROR);
            return null;
        }

        if (($objLanguage = Language::findByNotificationAndLanguageOrFallback($this, $strLanguage)) === null) {
            \System::log(sprintf(
                    'Could not find matching language or fallback for notification ID "%s" and language "%s".',
                    $this->id,
                    $strLanguage),
                __METHOD__,
                TL_ERROR);
            return null;
        }

        return $objGatewayModel->buildGateway($objBagType, $this, $objLanguage);
    }

    /**
     * Find by Bag
     * @param   Bag
     * @return  Notification|null
     */
    public static function findByBag(Bag $objBag, array $arrOptions=array())
    {
        return static::findByPid($objBag->id);
    }
}