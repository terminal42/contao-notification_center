<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 04.10.13
 * Time: 14:58
 * To change this template use File | Settings | File Templates.
 */

namespace NotificationCenter\Gateway;


class Email extends Base implements GatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateToken($strToken, $varValue)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function modifyDca(&$arrDca)
    {
        $arrDca['palettes']['default'] .= 'text,html';
    }

    /**
     * {@inheritdoc}
     */
    public function send($arrTokens)
    {

    }
}