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
 * @copyright  terminal42 gmbh 2013
 * @license    LGPL
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['registration'] = str_replace('reg_activate;', 'reg_activate,nc_notification;', $GLOBALS['TL_DCA']['tl_module']['palettes']['registration']);
$GLOBALS['TL_DCA']['tl_module']['palettes']['lostPasswordNotificationCenter'] = str_replace('reg_password', 'nc_notification', $GLOBALS['TL_DCA']['tl_module']['palettes']['lostPassword']);

if (strpos($GLOBALS['TL_DCA']['tl_module']['palettes']['personalData'], 'newsletters')) {
    $GLOBALS['TL_DCA']['tl_module']['palettes']['personalData'] = str_replace('newsletters;', 'newsletters,nc_notification;', $GLOBALS['TL_DCA']['tl_module']['palettes']['personalData']);
} else {
    $GLOBALS['TL_DCA']['tl_module']['palettes']['personalData'] = str_replace('editable;', 'editable,nc_notification;', $GLOBALS['TL_DCA']['tl_module']['palettes']['personalData']);
}


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['nc_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_module', 'getNotificationChoices'),
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);


/**
 * Notification choices
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['registration'] = array('member_registration');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['lostPasswordNotificationCenter'] = array('member_password');
