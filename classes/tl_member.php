<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use Contao\FrontendUser;

class tl_member
{
    /**
     * Store the personal data in session
     */
    public function storePersonalData()
    {
        if ('FE' === TL_MODE && true === FE_USER_LOGGED_IN)
        {
            $_SESSION['PERSONAL_DATA'] = FrontendUser::getInstance()->getData();
        }
    }
}
