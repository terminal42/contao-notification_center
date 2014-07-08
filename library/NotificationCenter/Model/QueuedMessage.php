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
     * @return $this
     */
    public function setTokens($arrTokens)
    {
        $this->tokens = json_encode($arrTokens);

        return $this;
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
     * Re-queue
     * @return $this
     */
    public function reQueue()
    {
        if ($this->dateSent > 0) {
            throw new \BadMethodCallException('You cannot re-queue a message that has already been sent!');
        }

        $this->error = '';
        $this->save();

        return $this;
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
     * Find next given number of messages from the queue that have not been sent yet
     * @param   int $intQuantity Number of messages
     * @param   array $arrOptions
     * @return  QueuedMessage[]|null
     */
    public static function findQueuedByQuantity($intQuantity = 10, $arrOptions = array())
    {
        $t = static::getTable();

        $arrOptions = array_merge(
            array(
                'column' => array("$t.dateSent=''", "$t.error!=1"),
                'order'  => "$t.dateAdded",
                'limit'  => $intQuantity
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }
}
