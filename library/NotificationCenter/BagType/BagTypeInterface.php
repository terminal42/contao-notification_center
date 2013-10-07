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


interface NotificationTypeInterface
{
    /**
     * Returns the tokens that contain valid recipient data (e.g. an email or a phone number)
     * @return  array
     */
    public function getRecipientTokens();

    /**
     * Returns the tokens that contain valid text data
     * @return  array
     */
    public function getTextTokens();

    /**
     * Returns the tokens that contain valid file data
     * @return  array
     */
    public function getFileTokens();

    /**
     * Returns the description for a specific token
     * @param   string The token
     * @return  string The description
     */
    public function getTokenDescription($strToken);
}