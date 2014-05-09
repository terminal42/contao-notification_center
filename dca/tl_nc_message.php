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

$this->loadDataContainer('tl_nc_gateway');

/**
 * Table tl_nc_message
 */
$GLOBALS['TL_DCA']['tl_nc_message'] = array
(

    // Config
    'config' => array
    (
        'ptable'                      => 'tl_nc_notification',
        'ctable'                      => array('tl_nc_language'),
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'pid'   => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit',
            'headerFields'            => array('title', 'type'),
            'disableGrouping'         => true,
            'child_record_callback'   => array('NotificationCenter\tl_nc_message', 'listRows'),
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s'
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
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_message']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_message']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif'
            ),
            'cut' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_nc_message']['cut'],
                'href'                  => 'act=paste&amp;mode=cut',
                'icon'                  => 'cut.gif',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_message']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_message']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('NotificationCenter\tl_nc_message', 'toggleIcon')
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_nc_message']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('gateway_type'),
        'default'                     => '{title_legend},title,gateway',
        'email'                       => '{title_legend},title,gateway;{languages_legend},languages;{expert_legend:hide},email_priority,email_template;{publish_legend},published',
        'file'                        => '{title_legend},title,gateway;{languages_legend},languages;{publish_legend},published',
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
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_message']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'gateway' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_message']['gateway'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_nc_gateway.title',
            'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
            'save_callback' => array
            (
                // Save gateway_type
                function($varValue, $dc) {
                    \Database::getInstance()->prepare("UPDATE tl_nc_message SET gateway_type=(SELECT type FROM tl_nc_gateway WHERE id=?) WHERE id=?")->execute($varValue, $dc->id);
                    \Database::getInstance()->prepare("UPDATE tl_nc_language SET gateway_type=(SELECT type FROM tl_nc_gateway WHERE id=?) WHERE pid=?")->execute($varValue, $dc->id);

                    return $varValue;
                }
            ),
        ),
        'gateway_type' => array
        (
            // This is only to select the palette
            'eval'                    => array('doNotShow'=>true),
            'sql'                     => &$GLOBALS['TL_DCA']['tl_nc_gateway']['fields']['type']['sql'],
        ),
        'languages' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'],
            'inputType'             => 'dcaWizard',
            'foreignTable'          => 'tl_nc_language',
            'eval'                  => array
            (
                'listCallback'      => array('NotificationCenter\tl_nc_language', 'generateWizardList'),
                'editButtonLabel'   => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][2],
                'applyButtonLabel'  => &$GLOBALS['TL_LANG']['tl_nc_message']['languages'][3],
                'tl_class'          =>'clr'
            )
        ),
        'email_priority' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_nc_message']['email_priority'],
            'exclude'               => true,
            'default'               => 3,
            'inputType'             => 'select',
            'options'               => array(1,2,3,4,5),
            'reference'             => &$GLOBALS['TL_LANG']['tl_nc_message']['email_priority_options'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '0'",
        ),
        'email_template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_nc_message']['email_template'],
            'exclude'               => true,
            'default'               => 'mail_default',
            'inputType'             => 'select',
            'options'               => $this->getTemplateGroup('mail_'),
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'published' => array
        (
            'exclude'                 => true,
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_message']['published'],
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);
