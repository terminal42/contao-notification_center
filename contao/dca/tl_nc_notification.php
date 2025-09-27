<?php

declare(strict_types=1);

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_nc_notification'] = [
    // Config
    'config' => [
        'ctable' => ['tl_nc_message'],
        'dataContainer' => DC_Table::class,
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['type', 'title'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
                'primary' => true,
            ],
            'children' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['children'],
                'href' => 'table=tl_nc_message',
                'icon' => 'children.svg',
                'primary' => true,
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{title_legend},title,type;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'title' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_notification']['type'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 128, 'default' => null, 'notnull' => false],
        ],
    ],
];
