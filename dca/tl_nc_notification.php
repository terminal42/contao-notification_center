<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Table tl_nc_notification
 */
$GLOBALS['TL_DCA']['tl_nc_notification'] = array
(

    // Config
    'config' => array
    (
        'ctable'                      => array('tl_nc_message'),
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
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
            'group_callback'          => array('NotificationCenter\tl_nc_notification', 'getGroupLabel')
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
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['edit'],
                'href'                => 'table=tl_nc_message',
                'icon'                => 'edit.gif'
            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('type'),
        'default'                     => '{title_legend},title,type;',
        'core_form'                   => '{title_legend},title,type;{config_legend},flatten_delimiter;{templates_legend:hide},templates',
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
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_notification']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_notification']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => array('NotificationCenter\tl_nc_notification', 'getNotificationTypes'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_nc_notification']['type'],
            'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'flatten_delimiter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_notification']['flatten_delimiter'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'default'                 => ',',
            'eval'                    => array('doNotTrim'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'templates' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_notification']['templates'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => \Backend::getTemplateGroup('notification_'),
            'eval'                    => array('multiple'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'clr'),
            'sql'                     => "blob NULL",
        ),
    )
);
