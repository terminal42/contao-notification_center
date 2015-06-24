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

interface QueueManagerInterface
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
    public function addMessage(Message $message, $tokens, $language);

    /**
     * Deletes a message from the queue.
     *
     * @param Message $message
     *
     * @return $this
     */
    public function removeMessage(Message $message);

    /**
     * Sends a given number of messages in the queue.
     *
     * @param int $sourceQueue      The ID of the source queue
     * @param int $numberOfMsgs     Number of messages to send
     *
     * @return $this
     */
    public function sendFromQueue($sourceQueue, $numberOfMsgs);
} 
