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

namespace NotificationCenter\NotificationType;


use NotificationCenter\Model\Notification;

class Base
{
    /**
     * The notification model
     * @var Notification
     */
    protected $objNotification = null;

    /**
     * Set the notification model
     * @param   Notification
     */
    public function __construct(Notification $objNotification)
    {
        $this->objNotification = $objNotification;
        \System::loadLanguageFile('tokens');
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenDescription($strToken)
    {
        return $GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][$this->objNotification->type][$strToken] ?: '';
    }
}