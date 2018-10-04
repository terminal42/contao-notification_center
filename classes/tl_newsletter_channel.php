<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2018, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

class tl_newsletter_channel extends \Backend
{
    /**
     * Get notification choices
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getNotificationChoices(\DataContainer $dc)
    {
        $arrChoices = array();
        $objNotifications = \Database::getInstance()
            ->prepare('SELECT id,title FROM tl_nc_notification WHERE type=? ORDER BY title')
            ->execute('newsletter');

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }
}
