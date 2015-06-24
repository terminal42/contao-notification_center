<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Message;


interface GatewayInterface
{

    /**
     * Sends the notification notification
     * @param   Message
     * @param   array       The tokens in key => value format
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '');
}
