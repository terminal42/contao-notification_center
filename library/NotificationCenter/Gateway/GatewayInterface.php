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


interface GatewayInterface
{
    /**
     * Validates a certain token
     * @param   string The token
     * @param   string The token value
     * @return  boolean
     */
    public function validateToken($strToken, $varValue);

    /**
     * Modifies the DCA to the gateway's needs
     * @param   array
     */
    public function modifyDca(&$arrDca);

    /**
     * Sends the notification bag
     * @param   array The tokens in key => value format
     */
    public function send($arrTokens);
}