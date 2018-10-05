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
$GLOBALS['TL_DCA']['tl_module']['palettes']['registration'] = str_replace('reg_activate;', 'reg_activate,nc_notification,nc_activation_notification;', $GLOBALS['TL_DCA']['tl_module']['palettes']['registration']);
$GLOBALS['TL_DCA']['tl_module']['palettes']['lostPasswordNotificationCenter'] = str_replace('reg_password', 'nc_notification', $GLOBALS['TL_DCA']['tl_module']['palettes']['lostPassword']);

$GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterSubscribeNotificationCenter'] = '{title_legend},name,headline,type'
    . ';{config_legend},nl_channels,nl_hideChannels,disableCaptcha'
    . ';{text_legend},nl_text'
    . ';{notification_legend},nc_notification'
    . ';{redirect_legend},jumpTo'
    . ';{template_legend:hide},nl_template'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterActivateNotificationCenter'] = '{title_legend},name,headline,type'
    . ';{config_legend},nl_channels,nl_hideChannels'
    . ';{notification_legend},nc_notification'
    . ';{redirect_legend},jumpTo'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterUnsubscribeNotificationCenter'] = '{title_legend},name,headline,type'
    . ';{config_legend},nl_channels,nl_hideChannels,disableCaptcha'
    . ';{notification_legend},nc_notification'
    . ';{redirect_legend},jumpTo'
    . ';{template_legend:hide},nl_template'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID';

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

$GLOBALS['TL_DCA']['tl_module']['fields']['nc_activation_notification'] = $GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification'];
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_activation_notification']['label'] = &$GLOBALS['TL_LANG']['tl_module']['nc_activation_notification'];

/**
 * Notification choices
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['registration'] = array('member_registration');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['lostPasswordNotificationCenter'] = array('member_password');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['newsletterSubscribeNotificationCenter'] = array('newsletter_subscribe');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['newsletterActivateNotificationCenter'] = array('newsletter_activate');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification']['eval']['ncNotificationChoices']['newsletterUnsubscribeNotificationCenter'] = array('newsletter_unsubscribe');
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_activation_notification']['eval']['ncNotificationChoices']['registration'] = array('member_activation');
