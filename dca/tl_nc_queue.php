<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Table tl_nc_queue
 */
$GLOBALS['TL_DCA']['tl_nc_queue'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'closed'                      => true,
        'notEditable'                 => true,
        'notCopyable'                 => true,
        'notSortable'                 => true,
        'ondelete_callback' => [
            ['NotificationCenter\tl_nc_queue', 'onDeleteCallback'],
        ],
        'sql' => array
        (
            'keys' => array
            (
                'id'            => 'primary',
                'message'       => 'index',
                'sourceQueue'   => 'index',
                'targetGateway' => 'index',
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('dateAdded DESC', 'id DESC'),
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('message', 'dateSent'),
            'label_callback'          => array('NotificationCenter\tl_nc_queue', 'listRows'),
        ),
        'operations' => array
        (
            're-queue' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_queue']['re-queue'],
                'href'                => 'key=re-queue',
                'icon'                => 'system/modules/notification_center/assets/re-queue.png',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_nc_queue']['re-queueConfirmation'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('NotificationCenter\tl_nc_queue', 'reQueueButton')
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_queue']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('NotificationCenter\tl_nc_queue', 'deleteButton')
            ),
            // @todo: maybe format the json encoded tokens for better usability?
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_queue']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array(),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'sourceQueue' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['sourceQueue'],
            'foreignKey'              => 'tl_nc_gateway.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'targetGateway' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['targetGateway'],
            'foreignKey'              => 'tl_nc_gateway.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'message' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['message'],
            'filter'                  => true,
            'foreignKey'              => 'tl_nc_message.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'dateAdded' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['dateAdded'],
            'flag'                    => 6,
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'dateDelay' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['dateDelay'],
            'flag'                    => 6,
            'sql'                     => "int(10) unsigned NULL"
        ),
        'dateSent' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['dateSent'],
            'flag'                    => 6,
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'error' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['error'],
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'tokens' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['tokens'],
            'search'                  => true,
            'sql'                     => "blob NULL"
        ),
        'language' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['language'],
            'filter'                  => true,
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'attachments' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['attachments'],
            'sql'                     => "blob NULL"
        ),
    )
);
