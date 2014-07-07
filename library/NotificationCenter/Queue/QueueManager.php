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
 * @copyright  terminal42 gmbh 2014
 * @license    LGPL
 */

namespace NotificationCenter\Queue;


use NotificationCenter\Model\Message;
use NotificationCenter\Model\QueuedMessage;

class QueueManager
{
    /**
     * Adds a message to the queue
     * @param Message $objMessage
     * @param         $arrTokens
     * @param         $strLanguage
     * @return $this
     */
    public function addMessage(Message $objMessage, $arrTokens, $strLanguage)
    {
        $objQueuedMessage = new QueuedMessage();
        $objQueuedMessage->message = $objMessage->id;
        $objQueuedMessage->dateAdded = time();
        $objQueuedMessage->setTokens($arrTokens);
        $objQueuedMessage->language = $strLanguage;

        $objQueuedMessage->save();

        return $this;
    }

    /**
     * Deletes a message from the queue
     * @param Message $objMessage
     * @return $this
     */
    public function removeMessage(Message $objMessage)
    {
        \Database::getInstance()->prepare('DELETE FROM tl_nc_queue WHERE message=?')
            ->execute($objMessage->id);

        return $this;
    }

    /**
     * Sends a collection of messages
     * @param Message[] $objMsgCollection
     * @return array An array containing true or false (delivery result) for every message
     */
    public function sendMessages(\Model\Collection $objMessageCollection)
    {
        $arrResult = array();
        $arrMessageIds = $objMessageCollection->fetchEach('id');
        $objQueuedMsgs = QueuedMessage::findMultipleByIds($arrMessageIds);
        foreach ($objQueuedMsgs as $objQueuedMsg) {
            $arrResult[$objQueuedMsg->message] = $objQueuedMsg->send();
        }

        return $arrResult;
    }

    /**
     * Sends a given number of messages in the queue
     * @param int $intNumberOfMsgs Number of messages to send
     * @return $this
     */
    public function sendFromQueue($intNumberOfMsgs = 10)
    {
        $objQueuedMsgs = QueuedMessage::findByQuantity($intNumberOfMsgs);

        foreach ($objQueuedMsgs as $objQueuedMsg) {
            /* @var $objQueuedMsg QueuedMessage */
            $blnResult = $objQueuedMsg->send();
            if (!$blnResult) {
                $objQueuedMsg->error = 1;
            } else {
                $objQueuedMsg->dateSent = time();
            }

            $objQueuedMsg->save();
        }

        return $this;
    }
} 