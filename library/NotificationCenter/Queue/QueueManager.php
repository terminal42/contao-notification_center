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

class QueueManager implements QueueManagerInterface
{
    /**
     * Adds a message to the queue.
     *
     * @param Message   $message
     * @param           $tokens
     * @param           $language
     *
     * @return $this
     */
    public function addMessage(Message $message, $tokens, $language)
    {
        $gateway = $message->getRelated('gateway');

        if ($gateway === null || $gateway->type !== 'queue') {

            throw new \InvalidArgumentException('You cannot add a message to the queue that does not belong to any queue gateway.');
        }

        $objQueuedMessage = new QueuedMessage();
        $objQueuedMessage->message          = $message->id;
        $objQueuedMessage->sourceQueue      = $gateway->id;
        $objQueuedMessage->targetGateway    = $gateway->queue_targetGateway;
        $objQueuedMessage->dateAdded = time();
        $objQueuedMessage->setTokens($tokens);
        $objQueuedMessage->language = $language;
        $objQueuedMessage->save();

        return $this;
    }

    /**
     * Deletes a message from the queue.
     *
     * @param Message $message
     *
     * @return $this
     */
    public function removeMessage(Message $message)
    {
        \Database::getInstance()->prepare('DELETE FROM tl_nc_queue WHERE message=?')
            ->execute($message->id);

        return $this;
    }

    /**
     * Sends a given number of messages in the queue.
     *
     * @param int $sourceQueue      The ID of the source queue
     * @param int $numberOfMsgs     Number of messages to send
     *
     * @return $this
     */
    public function sendFromQueue($sourceQueue, $numberOfMsgs)
    {
        $messages = QueuedMessage::findBySourceAndQuantity($sourceQueue, $numberOfMsgs);

        if ($messages === null) {

            return $this;
        }

        foreach ($messages as $msg) {
            /* @var $msg QueuedMessage */
            $result = $msg->send();
            if (!$result) {
                $msg->error = 1;
            } else {
                $msg->dateSent = time();
            }

            $msg->save();
        }

        return $this;
    }
} 
