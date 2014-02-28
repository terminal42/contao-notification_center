<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2013
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
                'id' => 'primary'
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
        '__selector__'                => array('type', 'email', 'email_overrideSmtp', 'file_connection'),
        'default'                     => '{title_legend},title,type',
        'email'                       => '{title_legend},title,type;{gateway_legend},email_overrideSmtp,',
        'file'                        => '{title_legend},title,type;{gateway_legend},file_type,file_connection',
    ),

    // Subpalettes
    'subpalettes' => array
    (
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
        'smtpUser' => array
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
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
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
    )
);