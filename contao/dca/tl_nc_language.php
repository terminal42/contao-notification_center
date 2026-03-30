<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\Token\TokenContext;

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
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['language'],
            'headerFields' => ['title', 'gateway', 'published'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
        ],
        'label' => [
            'fields' => ['language', 'fallback'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => null, 'notnull' => false],
        ],
        'fallback' => [
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'recipients' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_context' => TokenContext::Email,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_sender_name' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'nc_context' => TokenContext::Text,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_sender_address' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_context' => TokenContext::Email,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_recipient_cc' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_context' => TokenContext::Email,
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_recipient_bcc' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_context' => TokenContext::Email,
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_replyTo' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'nc_context' => TokenContext::Email,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_subject' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_context' => TokenContext::Text,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'email_mode' => [
            'inputType' => 'radio',
            'options' => ['textOnly', 'htmlAndAutoText', 'textAndHtml'],
            'reference' => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => 'textOnly'],
        ],
        'email_text' => [
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true, 'mandatory' => true],
            'nc_context' => TokenContext::Text,
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'email_html' => [
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE', 'decodeEntities' => true, 'allowHtml' => true, 'mandatory' => true],
            'nc_context' => TokenContext::Html,
            'sql' => ['type' => 'text', 'default' => null, 'notnull' => false],
        ],
        'attachment_tokens' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long clr', 'decodeEntities' => true],
            'nc_context' => TokenContext::File,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => null, 'notnull' => false],
        ],
        'attachments' => [
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => 65535, 'default' => null, 'notnull' => false],
        ],
    ],
];
