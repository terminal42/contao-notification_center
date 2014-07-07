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
 * @copyright  terminal42 gmbh 2014
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
        'notDeletable'                => true,
        'notCopyable'                 => true,
        'notSortable'                 => true,
        'sql' => array
        (
            'keys' => array
            (
                'id'        => 'primary',
                'message'   => 'index'
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
        ),/*
        'global_operations' => array
        (
            // @todo add a global operation to manually send the queue (use ajax because of max_script_execution)
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),*/
        'operations' => array
        (
            're-queue' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_queue']['re-queue'],
                'href'                => 'key=re-queue',
                'icon'                => 'system/modules/notification_center/assets/re-queue.png',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_nc_queue']['re-queueConfirmation'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('NotificationCenter\tl_nc_queue', 'reQueueButton')
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
    'palettes' => array
    (
        'default'                     => '{title_legend},title,type'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
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
        'dateSent' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_queue']['dateSent'],
            'flag'                    => 6,
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
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
            'sql'                     => "varchar(5) NOT NULL default ''"
        )
    )
);