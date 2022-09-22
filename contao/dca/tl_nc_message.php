<?php

declare(strict_types=1);

use Contao\DC_Table;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;

$GLOBALS['TL_DCA']['tl_nc_message'] = [
    // Config
    'config' => [
        'ptable' => 'tl_nc_notification',
        'ctable' => ['tl_nc_language'],
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['title', 'type'],
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['toggle'],
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['gateway'],
        'default' => '{title_legend},title,gateway;{publish_legend},published',
        MailerGateway::NAME => '{title_legend},title,gateway;{languages_legend},languages;{expert_legend:hide},email_priority,email_template;{publish_legend},published',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_nc_notification.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''", // TODO: correct but convert
        ],
        'gateway' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_nc_gateway.title',
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'languages' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => 'tl_nc_language',
            'eval' => [
                'fields' => ['language', 'fallback'],
                'showOperations' => true,
                'operations' => ['edit', 'delete'],
                'editButtonLabel' => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][2],
                'applyButtonLabel' => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][3],
                'tl_class' => 'clr',
            ],
        ],
        'email_priority' => [
            'exclude' => true,
            'default' => 3,
            'inputType' => 'select',
            'options' => [1, 2, 3, 4, 5],
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],
        'email_template' => [
            'exclude' => true,
            'default' => 'mail_default',
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_nc_message']['published'],
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
