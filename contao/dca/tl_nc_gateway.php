<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;

$GLOBALS['TL_DCA']['tl_nc_gateway'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
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
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['type', 'title'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'defaultSearchField' => 'title',
        ],
        'label' => [
            'fields' => ['title'],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{title_legend},title,type',
        MailerGateway::NAME => '{title_legend},title,type;{gateway_legend},mailerTransport',
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
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_gateway']['type'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => null, 'notnull' => false],
        ],
        'mailerTransport' => [
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => null, 'notnull' => false],
        ],
    ],
];
