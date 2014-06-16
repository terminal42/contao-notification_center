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

namespace NotificationCenter\Draft;


interface EmailDraftInterface extends DraftInterface
{
    /**
     * Returns the sender email as a string
     * @return  string
     */
    public function getSenderEmail();

    /**
     * Returns the sender name as a string
     * @return  string
     */
    public function getSenderName();

    /**
     * Returns the recipient emails
     * @return  array
     */
    public function getRecipientEmails();

    /**
     * Returns the carbon copy recipient emails
     * @return  array
     */
    public function getCcRecipientEmails();

    /**
     * Returns the blind carbon copy recipient emails
     * @return  array
     */
    public function getBccRecipientEmails();

    /**
     * Returns the replyTo email address
     * @return  string
     */
    public function getReplyToEmail();

    /**
     * Returns the subject as a string
     * @return  string
     */
    public function getSubject();

    /**
     * Returns the priority of the email
     * 1 = Highest
     * 2 = High
     * 3 = Normal
     * 4 = Low
     * 5 = Lowest
     * @return  int
     */
    public function getPriority();

    /**
     * Returns the text body as a string
     * @return  string
     */
    public function getTextBody();

    /**
     * Returns the html body as a string
     * @return  string
     */
    public function getHtmlBody();

    /**
     * Returns the paths to attachments as an array
     * @return  array
     */
    public function getAttachments();
} 