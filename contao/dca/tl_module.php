<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'nc_registration_auto_activate';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['nc_registration_auto_activate'] = 'reg_jumpTo,nc_activation_notification';

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
