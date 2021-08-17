<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

use Contao\Model;

class Notification extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_notification';

    /**
     * Gets the published notifications collection
     * @return Message[]
     */
    public function getMessages()
    {
        return Message::findPublishedByNotification($this);
    }

    /**
     * Sends a notification
     * @param   array   The tokens
     * @param   string  The language (optional)
     * @return  array
     */
    public function send(array $arrTokens, $strLanguage = '')
    {
        // Check if there are valid messages
        if (($objMessages = $this->getMessages()) === null) {
            \System::log('Could not find any messages for notification ID ' . $this->id, __METHOD__, TL_ERROR);

            return array();
        }

        $arrTokens = $this->addTemplateTokens($arrTokens);
        $arrResult = array();

        foreach ($objMessages as $objMessage) {
            $arrResult[$objMessage->id] = $objMessage->send($arrTokens, $strLanguage);
        }

        return $arrResult;
    }

    /**
     * Find notification group for type
     * @param   string Type
     * @return  string Class
     */
    public static function findGroupForType($strType)
    {
        foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $strGroup => $arrTypes) {
            if (in_array($strType, array_keys($arrTypes))) {
                return $strGroup;
            }
        }

        return '';
    }

    /**
     * Checks whether the notification has a specific token in any of its messages.
     * @param string $token
     * @return bool
     */
    public function hasToken($token)
    {
        $messages = Message::findPublishedByNotification($this);

        if (null === $messages) {
            return false;
        }

        foreach ($messages as $message) {
            if ($message->hasToken($token)) {
                return true;
            }
        }

        return false;
    }

    private function addTemplateTokens(array $tokens)
    {
        $templates = deserialize($this->templates, true);

        foreach ($templates as $name) {
            try {
                $template = new \FrontendTemplate($name);
                $template->setData($tokens);

                $tokens['template_'.substr($name, 13)] = $template->parse();
            } catch (\Exception $e) {
                \System::log('Could not generate token template "'.$name.'"', __METHOD__, TL_ERROR);
            }
        }

        return $tokens;
    }
}
