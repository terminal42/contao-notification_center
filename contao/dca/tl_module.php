<?php

declare(strict_types=1);

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['nc_notification'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
];
