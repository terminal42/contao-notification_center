<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2018, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['default'] .= ';{notification_legend},nc_activate_notification,nc_remove_notification';

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['nc_activate_notification'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['nc_activate_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_newsletter_channel', 'getNotificationChoices'),
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['nc_remove_notification'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['nc_remove_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_newsletter_channel', 'getNotificationChoices'),
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);
