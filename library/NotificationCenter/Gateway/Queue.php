<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
        $targetModel = Gateway::findByPk($row['queue_targetGateway']);

        if ($targetModel === null) {

            return $label;
        }

        $label .= sprintf('<div style="color:#ccc;margin:5px 0 0 10px;">&#8627; %s</div>',
            $targetModel->title);

        return $label;
    }
}
