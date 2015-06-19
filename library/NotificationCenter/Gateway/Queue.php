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
namespace NotificationCenter\Gateway;


use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;

class Queue implements GatewayInterface, LabelCallbackInterface
{
    /**
     * Sends the notification notification
     * @param   Message
     * @param   array       The tokens in key => value format
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        /** @var $objQueueManager \NotificationCenter\Queue\QueueManagerInterface */
        $objQueueManager = $GLOBALS['NOTIFICATION_CENTER']['QUEUE_MANAGER'];
        $objQueueManager->addMessage($objMessage, $arrTokens, $strLanguage);

        return true;
    }

    /**
     * Gets the back end list label
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param array          $args
     *
     * @return string
     */
    public function getLabel($row, $label, \DataContainer $dc, $args)
    {
        $targetModel = Gateway::findByPk($row['targetGateway']);

        if ($targetModel === null) {

            return $label;
        }

        $label .= sprintf('<div style="color:#ccc;margin:5px 0 0 10px;">&#8627; %s</div>',
            $targetModel->title);

        return $label;
    }
}
