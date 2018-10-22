<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Queue;


use Contao\Files;
use Contao\Folder;
use NotificationCenter\Gateway\GatewayInterface;
use NotificationCenter\MessageDraft\EmailMessageDraft;
use NotificationCenter\MessageDraft\MessageDraftFactoryInterface;
use NotificationCenter\Model\Gateway;
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

        $objQueuedMessage                = new QueuedMessage();
        $objQueuedMessage->message       = $message->id;
        $objQueuedMessage->sourceQueue   = $gateway->id;
        $objQueuedMessage->targetGateway = $gateway->queue_targetGateway;
        $objQueuedMessage->dateAdded     = time();

        // Add the delay date
        if ($gateway->queue_delay && ($dateDelay = strtotime($gateway->queue_delay, $objQueuedMessage->dateAdded)) !== false) {
            $objQueuedMessage->dateDelay = $dateDelay;
        }

        $objQueuedMessage->setTokens($tokens);
        $objQueuedMessage->language = $language;
        $objQueuedMessage->save();

        // Store the files in temporary folder
        if (($targetGatewayModel = Gateway::findByPk($gateway->queue_targetGateway)) !== null
            && isset($GLOBALS['NOTIFICATION_CENTER']['GATEWAY'][$targetGatewayModel->type])
        ) {
            $targetGateway = $GLOBALS['NOTIFICATION_CENTER']['GATEWAY'][$targetGatewayModel->type];
            $this->storeFiles(new $targetGateway($targetGatewayModel), $objQueuedMessage, $message, $language);
        }

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

            // Remove the temporary message files
            $this->removeMessageFiles($msg->id);

            $msg->save();
        }

        return $this;
    }

    /**
     * Remove the message files
     *
     * @param int $messageId
     */
    public function removeMessageFiles($messageId)
    {
        $folder = $this->getTemporaryFolderPath($messageId);

        if (is_dir(TL_ROOT . '/' . $folder)) {
            Files::getInstance()->rrdir($folder);
        }
    }

    /**
     * Store files in the temporary folder
     *
     * @param GatewayInterface $gateway
     * @param QueuedMessage    $queuedMessage
     * @param Message          $message
     * @param string           $language
     */
    protected function storeFiles(GatewayInterface $gateway, QueuedMessage $queuedMessage, Message $message, $language)
    {
        if (!($gateway instanceof MessageDraftFactoryInterface)) {
            return;
        }

        $draft = $gateway->createDraft($message, $queuedMessage->getTokens(), $language);

        // Return if the draft is not an e-mail draft
        if (!($draft instanceof EmailMessageDraft)) {
            return;
        }

        $attachments = $draft->getAttachments();

        // Return if there are no attachments
        if (count($attachments) === 0) {
            return;
        }

        $folder = new Folder($this->getTemporaryFolderPath($queuedMessage->id));

        // Copy the attachments to the temporary folder
        foreach ($attachments as $index => $originalPath) {
            $originalPath = str_replace(TL_ROOT . '/', '', $originalPath);
            $clonePath = $folder->path . '/' . basename($originalPath);

            // Update the tokens if copy was successful
            if (Files::getInstance()->copy($originalPath, $clonePath)) {
                $attachments[$index] = TL_ROOT . '/' . $clonePath;
            }
        }

        $queuedMessage->setAttachments($attachments);
        $queuedMessage->save();
    }

    /**
     * Get the temporary folder path
     *
     * @param int $messageId
     *
     * @return string
     */
    protected function getTemporaryFolderPath($messageId)
    {
        if (version_compare(VERSION, '4.4', '>=')) {
            return 'var/notification_center/' . $messageId;
        }

        return 'system/notification_center_tmp/' . $messageId;
    }
} 
