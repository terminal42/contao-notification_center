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
        MailerGateway::NAME => '{general_legend},language,fallback;{meta_legend},email_sender_name,email_sender_address,recipients,email_recipient_cc,email_recipient_bcc,email_replyTo;{content_legend},email_subject,email_mode;{attachments_legend},attachments,attachment_tokens',
    ],

    'subpalettes' => [
        'email_mode_textOnly' => 'email_text',
        'email_mode_textAndHtml' => 'email_text,email_html',
        'email_mode_htmlAndAutoText' => 'email_html',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_nc_message.title',
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'default' => 0, 'unsigned' => true],
        ],
        'language' => [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true,  'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => null, 'notnull' => false],
        ],
        'fallback' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'recipients' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                EmailToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_sender_name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                TextToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_sender_address' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                EmailToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_recipient_cc' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                EmailToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_recipient_bcc' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                EmailToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_replyTo' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                EmailToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_subject' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                TextToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_mode' => [
            'exclude' => true,
            'inputType' => 'radio',
            'options' => ['textOnly', 'htmlAndAutoText', 'textAndHtml'],
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => 'textOnly'],
        ],
        'email_text' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                TextToken::DEFINITION_NAME,
                FileToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_html' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE', 'decodeEntities' => true, 'allowHtml' => true, 'mandatory' => true],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                TextToken::DEFINITION_NAME,
                HtmlToken::DEFINITION_NAME,
                FileToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'attachment_tokens' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true],
            'nc_token_types' => [
                WildcardToken::DEFINITION_NAME,
                FileToken::DEFINITION_NAME,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'attachments' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => 65535, 'default' => null, 'notnull' => false],
        ],
    ],
];
