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

class QueuedMessage extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_queue';

    /**
     * Set the tokens
     * @param array $arrTokens
     */
    public function setTokens($arrTokens)
    {
        $this->tokens = json_encode($arrTokens);
    }

    /**
     * Get the tokens
     * @return array
     */
    public function getTokens()
    {
        return (array) json_decode($this->tokens);
    }

    /**
     * Get the status
     * @return string
     */
    public function getStatus()
    {
        if ($this->dateSent > 0) {
            return 'sent';
        } elseif ($this->error) {
            return 'error';
        } else {
            return 'queued';
        }
    }

    /**
     * Send this queued message
     * @return  bool
     */
    public function send()
    {
        $objMessage = $this->getRelated('message');
        if ($objMessage === null) {
            \System::log('Could not send queued message ' . $this->id . ' because related message could not be found.', __METHOD__, TL_ERROR);
            return false;
        } else {
            return $objMessage->send($this->getTokens(), $this->language, true);
        }
    }

    /**
     * Find all published by notification
     * @param   Notification
     * @return  Message|null
     */
    public static function findByQuantity($intQuantity = 10)
    {
        return static::findAll(array('order'=>'dateAdded', 'limit'=>$intQuantity));
    }
}
