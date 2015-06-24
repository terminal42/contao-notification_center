<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
