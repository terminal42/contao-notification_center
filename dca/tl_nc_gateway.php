<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Table tl_nc_gateway
 */
$GLOBALS['TL_DCA']['tl_nc_gateway'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'onload_callback' => array
        (
            array('NotificationCenter\tl_nc_gateway', 'loadSettingsLanguageFile')
        ),
        'onsubmit_callback' => array
        (
            array('NotificationCenter\tl_nc_gateway', 'checkFileServerConnection')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'                    => 'primary',
                'queue_cronInterval'    => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('type', 'title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'label_callback'          => array('NotificationCenter\tl_nc_gateway', 'executeLabelCallback')
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
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('type', 'queue_cronEnable', 'email', 'email_overrideSmtp', 'file_connection'),
        'default'                     => '{title_legend},title,type',
        'queue'                       => '{title_legend},title,type;{gateway_legend},queue_targetGateway;{cronjob_legend},queue_cronExplanation,queue_cronEnable',
        'email'                       => '{title_legend},title,type;{gateway_legend},email_overrideSmtp,',
        'file'                        => '{title_legend},title,type;{gateway_legend},file_type,file_connection',
        'postmark'                    => '{title_legend},title,type;{gateway_legend},postmark_key,postmark_test,postmark_ssl',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'queue_cronEnable'            => 'queue_cronInterval,queue_cronMessages',
        'email_overrideSmtp'          => 'email_smtpHost,email_smtpUser,email_smtpPass,email_smtpEnc,email_smtpPort',
        'file_connection_local'       => 'file_path',
        'file_connection_ftp'         => 'file_host,file_port,file_username,file_password,file_path',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => array_keys($GLOBALS['NOTIFICATION_CENTER']['GATEWAY']),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['type'],
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'queue_targetGateway' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_targetGateway'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => function() {
                $options = array();

                $gateways = \Database::getInstance()->prepare('SELECT id,title FROM tl_nc_gateway WHERE type!=?')
                    ->execute('queue');

                while ($gateways->next()) {
                    $options[$gateways->id] = $gateways->title;
                }

                return $options;
            },
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                     => "int(10) NOT NULL default '0'"
        ),
        'queue_cronExplanation' => array
        (
            'exclude'                 => true,
            'input_field_callback'    => array('NotificationCenter\tl_nc_gateway', 'queueCronjobExplanation')
        ),
        'queue_cronEnable' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronEnable'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'queue_cronInterval' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('minutely', 'hourly', 'daily', 'weekly', 'monthly'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronInterval'],
            'eval'                    => array('tl_class'=>'w50', 'includeBlankOption'=>true, 'mandatory'=>true),
            'sql'                     => "varchar(12) NOT NULL default ''"
        ),
        'queue_cronMessages' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['queue_cronMessages'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('tl_class'=>'w50', 'rgxp'=>'natural', 'mandatory'=>true),
            'sql'                     => "int(10) NOT NULL default '0'"
        ),
        'email_overrideSmtp' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['email_overrideSmtp'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'email_smtpHost' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpHost'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'nospace'=>true, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_smtpUser' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpUser'],
            'inputType'               => 'text',
            'eval'                    => array('decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_smtpPass' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpPass'],
            'inputType'               => 'textStore',
            'eval'                    => array('decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_smtpEnc' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpEnc'],
            'inputType'               => 'select',
            'options'                 => array(''=>'-', 'ssl'=>'SSL', 'tls'=>'TLS'),
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(3) NOT NULL default ''"
        ),
        'email_smtpPort' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpPort'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(16) NOT NULL default ''"
        ),
        'file_type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => array('csv', 'xml'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_type'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(4) NOT NULL default ''"
        ),
        'file_connection' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options'                 => array('local', 'ftp'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_connection'],
            'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(8) NOT NULL default ''"
        ),
        'file_host' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_host'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'file_port' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_port'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "varchar(5) NOT NULL default ''"
        ),
        'file_username' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_username'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'file_password' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_password'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'file_path' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['file_path'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('decodeEntities'=>true, 'trailingSlash'=>false, 'tl_class'=>'clr long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'postmark_key' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_key'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'postmark_test' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_test'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'postmark_ssl' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_gateway']['postmark_ssl'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
    )
);
