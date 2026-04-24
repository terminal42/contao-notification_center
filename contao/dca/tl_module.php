<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'nc_registration_auto_activate';
$GLOBALS['TL_DCA']['tl_module']['palettes']['lostPasswordNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['lostPassword'];
$GLOBALS['TL_DCA']['tl_module']['palettes']['registrationNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['registration'];
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['nc_registration_auto_activate'] = 'reg_jumpTo,nc_activation_notification';

PaletteManipulator::create()
    ->addField('nc_notification', 'reg_password', PaletteManipulator::POSITION_BEFORE)
    ->addField('nc_lost_password_jumpTo', 'email_legend', PaletteManipulator::POSITION_PREPEND)
    ->removeField('reg_password')
    ->applyToPalette('lostPasswordNotificationCenter', 'tl_module')
;

PaletteManipulator::create()
    ->addField('nc_notification', 'reg_activate')
    ->addField('nc_registration_auto_activate', 'nc_notification')
    ->removeField('reg_activate')
    ->applyToPalette('registrationNotificationCenter', 'tl_module')
;

PaletteManipulator::create()
    ->addField('nc_notification', 'config_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('personalData', 'tl_module')
;

// ContaoNewsletterBundle must be installed
if (isset($GLOBALS['TL_DCA']['tl_module']['palettes']['subscribe'])) {
    $GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterSubscribeNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['subscribe'];
    $GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterUnsubscribeNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['unsubscribe'];

    PaletteManipulator::create()
        ->addField('nc_notification', 'email_legend', PaletteManipulator::POSITION_APPEND)
        ->removeField('nl_subscribe')
        ->applyToPalette('newsletterUnsubscribeNotificationCenter', 'tl_module')
        ->addField('nc_activation_notification', 'nc_notification')
        ->addField('nc_newsletter_activation_jumpTo', 'redirect_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('newsletterSubscribeNotificationCenter', 'tl_module')
    ;
}

$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_activation_notification'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_registration_auto_activate'] = [
    'inputType' => 'checkbox',
    'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'boolean', 'default' => true],
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_newsletter_activation_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    'eval' => ['fieldType' => 'radio'],
    'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_lost_password_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    'eval' => ['fieldType' => 'radio'],
    'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
];
