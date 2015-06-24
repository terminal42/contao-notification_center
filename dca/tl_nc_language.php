<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

$this->loadDataContainer('tl_nc_gateway');

/**
 * Table tl_nc_language
 */
$GLOBALS['TL_DCA']['tl_nc_language'] = array
(

    // Config
    'config' => array
    (
        'ptable'                      => 'tl_nc_message',
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'nc_type_query'               => "SELECT type FROM tl_nc_notification WHERE id=(SELECT pid FROM tl_nc_message WHERE id=(SELECT pid FROM tl_nc_language WHERE id=?))",
        'oncreate_callback' => array
        (
            array('NotificationCenter\tl_nc_language', 'insertGatewayType'),
        ),
        'onload_callback'             => array
        (
            array('NotificationCenter\tl_nc_language', 'modifyPalette'),
            array('NotificationCenter\AutoSuggester', 'load')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'         => 'primary',
                'pid'        => 'index',
                'language'   => 'index'
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('language'),
            'flag'                    => 1
        ),
        'label' => array
        (
            'fields'                  => array('language', 'fallback'),
            'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_language']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_language']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_language']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_language']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('gateway_type', 'email_mode'),
        'default'                     => '{general_legend},language,fallback',
        'email'                       => '{general_legend},language,fallback;{meta_legend},email_sender_name,email_sender_address,recipients,email_recipient_cc,email_recipient_bcc,email_replyTo;{content_legend},email_subject,email_mode;{attachments_legend},attachments,attachment_tokens',
        'file'                        => '{general_legend},language,fallback;{meta_legend},file_name,file_override;{content_legend},file_content',
        'postmark'                    => '{general_legend},language,fallback;{meta_legend},email_sender_name,email_sender_address,recipients,email_recipient_cc,email_recipient_bcc,email_replyTo;{content_legend},email_subject,email_mode',
    ),

    'subpalettes' => array
    (
        'email_mode_textOnly'         => 'email_text',
        'email_mode_textAndHtml'      => 'email_text,email_html',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'foreignKey'              => 'tl_nc_message.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'gateway_type' => array
        (
            // This is only to select the palette
            'eval'                    => array('doNotShow'=>true),
            'sql'                     => &$GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['type']['sql'],
        ),
        'language' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['language'],
            'exclude'                 => true,
            'default'                 => $GLOBALS['TL_LANGUAGE'],
            'inputType'               => 'select',
            'options'                 => \System::getLanguages(),
            'eval'                    => array('mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(5) NOT NULL default ''",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateLanguageField')
            )
        ),
        'fallback' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['fallback'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateFallbackField')
            )
        ),
        'recipients' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['recipients'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'long clr', 'decodeEntities'=>true, 'mandatory'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateEmailList')
            )
        ),
        'attachment_tokens' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['attachment_tokens'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'long clr', 'decodeEntities'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'attachments' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['attachments'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'filesOnly'=>true, 'tl_class'=>'clr'),
            'sql'                     => "blob NULL"
        ),
        'email_sender_name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_name'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
        ),
        'email_sender_address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_sender_address'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
        ),
        'email_recipient_cc' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_cc'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rgxp'=>'nc_tokens', 'style'=>'height:40px; width:314px', 'decodeEntities'=>true, 'tl_class'=>'w50" style="height:auto'),
            'sql'                     => "text NULL",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateEmailList')
            )
        ),
        'email_recipient_bcc' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_recipient_bcc'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rgxp'=>'nc_tokens', 'style'=>'height:40px; width:314px', 'decodeEntities'=>true, 'tl_class'=>'w50" style="height:auto'),
            'sql'                     => "text NULL",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateEmailList')
            )
        ),
        'email_replyTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_replyTo'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'nc_tokens', 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'save_callback' => array
            (
                array('NotificationCenter\tl_nc_language', 'validateEmailList')
            )
        ),
        'email_subject' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_subject'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'long clr', 'decodeEntities'=>true, 'mandatory'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_mode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'exclude'                 => true,
            'default'                 => 'textOnly',
            'inputType'               => 'radio',
            'options'                 => array('textOnly', 'textAndHtml'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'eval'                    => array('tl_class'=>'clr', 'submitOnChange'=>true),
            'sql'                     => "varchar(16) NOT NULL default ''"
        ),
        'email_text' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_text'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'clr', 'decodeEntities'=>true, 'mandatory'=>true),
            'sql'                     => "text NULL"
        ),
        'email_html' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_html'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'clr', 'rte'=>'tinyMCE', 'decodeEntities'=>true, 'allowHtml'=>true, 'mandatory'=>true),
            'sql'                     => "text NULL"
        ),
        'file_name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['file_name'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'w50', 'decodeEntities'=>true, 'mandatory'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'file_override' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['file_override'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'file_content' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['file_content'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rgxp'=>'nc_tokens', 'tl_class'=>'clr', 'decodeEntities'=>true, 'preserveTags'=>true, 'mandatory'=>true, 'style'=>'min-height:100px'),
            'sql'                     => "text NULL"
        ),
    )
);
