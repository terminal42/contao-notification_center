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

class Bag extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_bag';

    /**
     * Gets the notifications collection
     * @return \Model\Collection
     */
    public function getNotifications()
    {
        return Notification::findByBag($this);
    }

    /**
     * Constructs the bag type
     * @return  BagTypeInterface|null
     */
    public function buildBagType()
    {
        // Find class
        $strClass = $GLOBALS['NOTIFICATION_CENTER']['BAGTYPE'][$this->type];
        if (!class_exists($strClass)) {
            return null;
        }

        try {
            return new $strClass($this);
        } catch (\Exception $e) {
            return null;
        }
    }
}