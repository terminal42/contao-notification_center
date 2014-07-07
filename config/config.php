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
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD'], 1, array
(
    'notification_center' => array
    (
        'nc_notifications' => array
        (
            'tables'        => array('tl_nc_notification', 'tl_nc_message', 'tl_nc_language'),
            'icon'          => 'system/modules/notification_center/assets/notification.png',
        ),
        'nc_gateways' => array
        (
            'tables'        => array('tl_nc_gateway'),
            'icon'          => 'system/modules/notification_center/assets/gateway.png'
        )
    )
));

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['user']['lostPasswordNotificationCenter'] = 'ModulePasswordNotificationCenter';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_nc_notification']             = 'NotificationCenter\Model\Notification';
$GLOBALS['TL_MODELS']['tl_nc_gateway']                  = 'NotificationCenter\Model\Gateway';
$GLOBALS['TL_MODELS']['tl_nc_language']                 = 'NotificationCenter\Model\Language';
$GLOBALS['TL_MODELS']['tl_nc_message']                  = 'NotificationCenter\Model\Message';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('NotificationCenter\AutoSuggester', 'verifyTokens');
$GLOBALS['TL_HOOKS']['processFormData'][] = array('NotificationCenter\tl_form', 'sendFormNotification');
$GLOBALS['TL_HOOKS']['createNewUser'][] = array('NotificationCenter\ContaoHelper', 'sendRegistrationEmail');

/**
 * Notification Center Gateways
 */
$GLOBALS['NOTIFICATION_CENTER']['GATEWAY'] = array_merge(
    (array) $GLOBALS['NOTIFICATION_CENTER']['GATEWAY'],
    array(
         'email'    => 'NotificationCenter\Gateway\Email',
         'file'     => 'NotificationCenter\Gateway\File',
         'postmark' => 'NotificationCenter\Gateway\Postmark',
    )
);

/**
 * Notification Center Notification Types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = array_merge(
    (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'],
    array(
         'core'   => array(
             'core_form' => array(
                 'recipients' => array('admin_email', 'form_*'),
                 'email_text' => array('form_*', 'formconfig_*', 'admin_email', 'raw_data')
             )
         ),
         'contao' => array(
             'member_registration' => array(
                 'recipients' => array('member_email', 'admin_email'),
                 'email_text' => array('domain', 'link', 'member_*', 'recipient_email')
             ),
             'member_password'     => array(
                 'recipients' => array('recipient_email'),
                 'email_text' => array('domain', 'link', 'member_*', 'recipient_email')
             )
         )
    )
);
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['file_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['file_content'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['core']['core_form']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_password']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_password']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_password']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_password']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_registration']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['member_password']['recipients'];

