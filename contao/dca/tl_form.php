<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/*
 * Palettes
 */
PaletteManipulator::create()
    ->addField('nc_notification', 'sendViaEmail')
    ->applyToPalette('default', 'tl_form')
;

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['nc_notification'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['sendViaEmail']['eval']['tl_class'] = 'clr w50';
