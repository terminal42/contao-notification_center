<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace('sendViaEmail;', 'nc_notification,sendViaEmail;', $GLOBALS['TL_DCA']['tl_form']['palettes']['default']);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['nc_notification'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_form']['nc_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_form', 'getNotificationChoices'),
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'clr w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['sendViaEmail']['eval']['tl_class'] = 'clr w50';
