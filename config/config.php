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
        'nc_queue' => array
        (
            'tables'        => array('tl_nc_queue'),
            'icon'          => 'system/modules/notification_center/assets/queue.png',
            're-queue'      => array('NotificationCenter\tl_nc_queue', 'reQueue')
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
$GLOBALS['TL_MODELS']['tl_nc_queue']                    = 'NotificationCenter\Model\QueuedMessage';

/**
 * Cron jobs
 */
$GLOBALS['TL_CRON']['minutely'][] = array('NotificationCenter\Frontend\Helper', 'sendMessageQueue');

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('NotificationCenter\AutoSuggester', 'verifyTokens');
$GLOBALS['TL_HOOKS']['processFormData'][] = array('NotificationCenter\tl_form', 'sendFormNotification');
$GLOBALS['TL_HOOKS']['createNewUser'][] = array('NotificationCenter\ContaoHelper', 'sendRegistrationEmail');
$GLOBALS['TL_HOOKS']['updatePersonalData'][] = array('NotificationCenter\ContaoHelper', 'sendPersonalDataEmail');

/**
 * Queue manager
 */
$GLOBALS['NOTIFICATION_CENTER']['QUEUE_MANAGER'] = new \NotificationCenter\Queue\QueueManager();

/**
 * Notification Center Gateways
 */
$GLOBALS['NOTIFICATION_CENTER']['GATEWAY'] = array_merge(
    (array) $GLOBALS['NOTIFICATION_CENTER']['GATEWAY'],
    array(
         'queue'    => 'NotificationCenter\Gateway\Queue',
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
         'contao' => array(
             'core_form' => array(
                 'recipients'           => array('admin_email', 'form_*'),
                 'email_subject'        => array('form_*', 'formconfig_*', 'admin_email'),
                 'email_text'           => array('form_*', 'formconfig_*', 'raw_data', 'admin_email'),
                 'email_html'           => array('form_*', 'formconfig_*', 'raw_data', 'admin_email'),
                 'file_name'            => array('form_*', 'formconfig_*', 'admin_email'),
                 'file_content'         => array('form_*', 'formconfig_*', 'admin_email'),
                 'email_recipient_cc'   => array('admin_email', 'form_*'),
                 'email_recipient_bcc'  => array('admin_email', 'form_*'),
                 'email_replyTo'        => array('admin_email', 'form_*'),
                 'attachment_tokens'    => array('form_*'),
             ),
             'member_registration' => array(
                 'recipients'           => array('member_email', 'admin_email'),
                 'email_subject'        => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_text'           => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_html'           => array('domain', 'link', 'member_*', 'admin_email'),
                 'file_name'            => array('domain', 'link', 'member_*', 'admin_email'),
                 'file_content'         => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_recipient_cc'   => array('admin_email', 'member_*'),
                 'email_recipient_bcc'  => array('admin_email', 'member_*'),
                 'email_replyTo'        => array('admin_email', 'member_*'),
             ),
             'member_personaldata' => array(
                 'recipients'           => array('member_email', 'admin_email'),
                 'email_subject'        => array('domain', 'member_*', 'member_old_*', 'recipient_email'),
                 'email_text'           => array('domain', 'member_*', 'member_old_*', 'recipient_email'),
                 'email_html'           => array('domain', 'member_*', 'member_old_*', 'recipient_email'),
                 'email_recipient_cc'   => array('member_email', 'admin_email'),
                 'email_recipient_bcc'  => array('member_email', 'admin_email'),
                 'email_replyTo'        => array('member_email', 'admin_email'),
             ),
             'member_password'     => array(
                 'recipients'           => array('recipient_email'),
                 'email_subject'        => array('domain', 'link', 'member_*', 'recipient_email'),
                 'email_text'           => array('domain', 'link', 'member_*', 'recipient_email'),
                 'email_html'           => array('domain', 'link', 'member_*', 'recipient_email'),
                 'file_name'            => array('domain', 'link', 'member_*', 'recipient_email'),
                 'file_content'         => array('domain', 'link', 'member_*', 'recipient_email'),
                 'email_recipient_cc'   => array('recipient_email'),
                 'email_recipient_bcc'  => array('recipient_email'),
                 'email_replyTo'        => array('recipient_email'),
             )
         )
    )
);
