<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
     *
     * @return  bool
     */
    public function send()
    {
        $message = $this->getRelated('message');
        if ($message === null) {
            \System::log('Could not send queued message ' . $this->id . ' because related message could not be found.', __METHOD__, TL_ERROR);

            return false;
        } else {
            // Temporarily set gateway to target gateway
            $message->gateway = $this->targetGateway;

            $result = $message->send($this->getTokens(), $this->language);

            // Reset gateway
            $message->gateway = $this->sourceQueue;

            return $result;
        }
    }

    /**
     * Find next given number of messages from the queue that have not been sent yet.
     *
     * @param int   $sourceQueue
     * @param int   $numberOfMsgs
     * @param array $options
     *
     * @return QueuedMessage[]|null
     */
    public static function findBySourceAndQuantity($sourceQueue, $numberOfMsgs, $options = array())
    {
        $t           = static::getTable();
        $sourceQueue = (int) $sourceQueue;

        $options = array_merge(
            array(
                'column' => array("$t.sourceQueue=$sourceQueue", "$t.dateSent=''", "$t.error!=1"),
                'order'  => "$t.dateAdded",
                'limit'  => $numberOfMsgs
            ),
            $options
        );

        return static::find($options);
    }
}
