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

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;

class Base
{
    /**
     * The notification message
     * @var Message
     */
    protected $objMessage = null;

    /**
     * The language model
     * @var Language
     */
    protected $objLanguage = null;

    /**
     * The gateway model
     * @var Gateway
     */
    protected $objGateway = null;

    /**
     * Set notification type and models
     * @param   Notification
     * @param   Language
     * @param   Gateway
     */
    public function __construct(Message $objMessage, Language $objLanguage, Gateway $objGateway )
    {
        $this->objMessage           = $objMessage;
        $this->objLanguage          = $objLanguage;
        $this->objGateway           = $objGateway;
    }

    /**
     * Gets the Message
     * @return  Message
     */
    public function getMessage()
    {
        return $this->objMessage;
    }

    /**
     * Gets the language
     * @return  Language
     */
    public function getLanguage()
    {
        return $this->objLanguage;
    }

    /**
     * Gets the gateway
     * @return  Gateway
     */
    public function getGateway()
    {
        return $this->objGateway;
    }

    /**
     * Gets an array of valid attachments
     * @param   array Tokens
     * @return  array
     */
    protected function getAttachments($arrTokens)
    {
        $arrAttachments = array();

        if (!$this->objLanguage->attachments) {
            return $arrAttachments;
        }

        $arrAttachmentTokens = trimsplit(',', $this->objLanguage->attachments);
        foreach ($arrAttachmentTokens as $strToken) {
            $strFile = \String::parseSimpleTokens($strToken, $arrTokens);

            if (is_file($strFile)) {
                $arrAttachments[$strToken] = $strFile;
            }
        }

        return $arrAttachments;
    }
}
