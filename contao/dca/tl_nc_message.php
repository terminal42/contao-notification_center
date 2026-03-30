<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;

$GLOBALS['TL_DCA']['tl_nc_message'] = [
    // Config
    'config' => [
        'ptable' => 'tl_nc_notification',
        'ctable' => ['tl_nc_language'],
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'sorting' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['title', 'type'],
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['gateway'],
        'default' => '{title_legend},title,gateway;{publish_legend},published,start,stop',
        MailerGateway::NAME => '{title_legend},title,gateway;{languages_legend},languages;{expert_legend:collapsed},email_priority,email_template;{publish_legend},published,start,stop',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_nc_notification.title',
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'sorting' => [
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'title' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'gateway' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_nc_gateway.title',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'languages' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => 'tl_nc_language',
            'eval' => [
                'fields' => ['language', 'fallback'],
                'showOperations' => true,
                'global_operations' => ['new'],
                'editButtonLabel' => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][2],
                'applyButtonLabel' => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][3],
                'tl_class' => 'clr',
            ],
        ],
        'email_priority' => [
            'inputType' => 'select',
            'options' => [1, 2, 3, 4, 5],
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => 'smallint', 'default' => 3, 'unsigned' => true],
        ],
        'email_template' => [
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => 'mail_default', 'notnull' => false],
        ],
        'published' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => ['doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => ['type' => 'string', 'length' => 10, 'default' => ''],
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => ['type' => 'string', 'length' => 10, 'default' => ''],
        ],
    ],
];
