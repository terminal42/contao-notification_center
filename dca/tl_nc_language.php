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
 * Table tl_nc_language
 */
$GLOBALS['TL_DCA']['tl_nc_language'] = array
(

    // Config
    'config' => array
    (
        'ptable'                      => 'tl_nc_notification',
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'onload_callback'             => array
        (
            array('NotificationCenter\tl_nc_language', 'loadGatewayDca')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'         => 'primary',
                'pid'        => 'index',
                'language'   => 'index'
            )
        )
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
            'fields'                  => array('language'),
            'format'                  => '%s',
            'label_callback'          => array('NotificationCenter\tl_nc_language', 'getLabel')
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
        'default'                     => '{general_legend},language,fallback,recipients;',
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
            'foreignKey'              => 'tl_nc_notification.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'language' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['language'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => \System::getLanguages(),
            'eval'                    => array('mandatory'=>true, 'unique'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(5) NOT NULL default ''"
        ),
        'fallback' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['fallback'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('fallback'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'recipients' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['recipients'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('tl_class'=>'long clr', 'decodeEntities'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'attachments' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['attachments'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('tl_class'=>'long clr', 'decodeEntities'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_subject' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_subject'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('tl_class'=>'long clr', 'decodeEntities'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'email_mode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_mode'],
            'exclude'                 => true,
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
            'eval'                    => array('tl_class'=>'clr', 'decodeEntities'=>true),
            'sql'                     => "text NULL"
        ),
        'email_html' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['email_html'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('tl_class'=>'clr', 'rte'=>'tinyMCE', 'decodeEntities'=>true, 'allowHtml'=>true),
            'sql'                     => "text NULL"
        )
    )
);