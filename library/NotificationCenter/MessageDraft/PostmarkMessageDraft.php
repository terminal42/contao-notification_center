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
