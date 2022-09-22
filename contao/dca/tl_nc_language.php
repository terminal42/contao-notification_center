<?php

declare(strict_types=1);

use Contao\DC_Table;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\FileToken;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

/*
 * Table tl_nc_language
 */
$GLOBALS['TL_DCA']['tl_nc_language'] = [
    // Config
    'config' => [
        'ptable' => 'tl_nc_message',
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'language' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['language'],
            'flag' => 1,
        ],
        'label' => [
            'fields' => ['language', 'fallback'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
                'label' => &$GLOBALS['TL_LANG']['tl_nc_language']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_language']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_language']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_language']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['email_mode'],
        'default' => '{general_legend},language,fallback',
        MailerGateway::NAME => '{general_legend},language,fallback;{meta_legend},email_sender_name,email_sender_address,recipients,email_recipient_cc,email_recipient_bcc,email_replyTo;{content_legend},email_subject,email_mode;{attachments_legend},attachments,attachment_templates,attachment_tokens',
    ],

    'subpalettes' => [
        'email_mode_textOnly' => 'email_text',
        'email_mode_textAndHtml' => 'email_text,email_html',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_nc_message.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'language' => [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'fallback' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'recipients' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::class,
                EmailToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'attachment_tokens' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true],
            'nc_token_types' => [
                WildcardToken::class,
                FileToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'attachments' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'attachment_templates' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'tl_class' => 'clr', 'extensions' => 'xml,txt,json'],
            'sql' => 'blob NULL',
        ],
        'email_sender_name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::class,
                TextToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email_sender_address' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::class,
                EmailToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email_recipient_cc' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::class,
                EmailToken::class,
            ],
            'sql' => 'text NULL',
        ],
        'email_recipient_bcc' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::class,
                EmailToken::class,
            ],
            'sql' => 'text NULL',
        ],
        'email_replyTo' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::class,
                EmailToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email_subject' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::class,
                TextToken::class,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email_mode' => [
            'exclude' => true,
            'default' => 'textOnly',
            'inputType' => 'radio',
            'options' => ['textOnly', 'textAndHtml'],
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'email_text' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::class,
                TextToken::class,
                FileToken::class,
            ],
            'sql' => 'text NULL',
        ],
        'email_html' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE', 'decodeEntities' => true, 'allowHtml' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::class,
                TextToken::class,
                HtmlToken::class,
                FileToken::class,
            ],
            'sql' => 'text NULL',
        ],
    ],
];
