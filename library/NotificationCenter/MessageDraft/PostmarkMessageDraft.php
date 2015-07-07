<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\MessageDraft;


use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;
use NotificationCenter\Util\String;

class PostmarkMessageDraft extends EmailMessageDraft
{
    /**
     * Should track opening the emails?
     * @return boolean
     */
    public function getTrackOpen()
    {
        return $this->objMessage->postmark_trackOpens ? true : false;
    }

    /**
     * Tag
     * @return string
     */
    public function getTag()
    {
        return $this->objMessage->postmark_tag;
    }
}
