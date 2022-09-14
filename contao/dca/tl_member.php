<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Add the global callbacks
 */
if ('FE' === TL_MODE)
{
    $GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('NotificationCenter\tl_member', 'storePersonalData');
}
