<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    if (version_compare(VERSION, '4.4', '<')) {
        $GLOBALS['TL_CSS'][] = 'system/modules/notification_center/assets/backend.css';
    } else {
        $GLOBALS['TL_CSS'][] = 'system/modules/notification_center/assets/backend_svg.css';
    }
}

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['user']['lostPasswordNotificationCenter'] = 'ModulePasswordNotificationCenter';

if (in_array('newsletter', \ModuleLoader::getActive(), true)) {
    $GLOBALS['FE_MOD']['newsletter']['newsletterSubscribeNotificationCenter']   = 'ModuleNewsletterSubscribeNotificationCenter';
    $GLOBALS['FE_MOD']['newsletter']['newsletterActivateNotificationCenter']    = 'ModuleNewsletterActivateNotificationCenter';
    $GLOBALS['FE_MOD']['newsletter']['newsletterUnsubscribeNotificationCenter'] = 'ModuleNewsletterUnsubscribeNotificationCenter';
}

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
$GLOBALS['TL_CRON']['minutely'][] = array('NotificationCenter\Frontend\PoorMansCron', 'minutely');
$GLOBALS['TL_CRON']['hourly'][]   = array('NotificationCenter\Frontend\PoorMansCron', 'hourly');
$GLOBALS['TL_CRON']['daily'][]    = array('NotificationCenter\Frontend\PoorMansCron', 'daily');
$GLOBALS['TL_CRON']['weekly'][]   = array('NotificationCenter\Frontend\PoorMansCron', 'weekly');
$GLOBALS['TL_CRON']['monthly'][]  = array('NotificationCenter\Frontend\PoorMansCron', 'monthly');

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]       = array('NotificationCenter\AutoSuggester', 'verifyTokens');
$GLOBALS['TL_HOOKS']['processFormData'][]       = array('NotificationCenter\tl_form', 'sendFormNotification');
$GLOBALS['TL_HOOKS']['createNewUser'][]         = array('NotificationCenter\ContaoHelper', 'sendRegistrationEmail');
$GLOBALS['TL_HOOKS']['updatePersonalData'][]    = array('NotificationCenter\ContaoHelper', 'sendPersonalDataEmail');
$GLOBALS['TL_HOOKS']['getUserNavigation'][]     = array('NotificationCenter\ContaoHelper', 'addQueueToUserNavigation');
$GLOBALS['TL_HOOKS']['activateAccount'][]       = array('NotificationCenter\ContaoHelper', 'sendActivationEmail');

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
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = array_merge_recursive(
    (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'],
    array(
         'contao' => array(
             'core_form' => array(
                 'recipients'           => array('admin_email', 'form_*', 'formconfig_*'),
                 'email_subject'        => array('form_*', 'formconfig_*', 'admin_email'),
                 'email_text'           => array('form_*', 'formconfig_*', 'formlabel_*', 'raw_data', 'admin_email'),
                 'email_html'           => array('form_*', 'formconfig_*', 'formlabel_*', 'raw_data', 'admin_email'),
                 'file_name'            => array('form_*', 'formconfig_*', 'admin_email'),
                 'file_content'         => array('form_*', 'formconfig_*', 'formlabel_*', 'raw_data', 'admin_email'),
                 'email_sender_name'    => array('admin_email', 'form_*', 'formconfig_*'),
                 'email_sender_address' => array('admin_email', 'form_*', 'formconfig_*'),
                 'email_recipient_cc'   => array('admin_email', 'form_*', 'formconfig_*'),
                 'email_recipient_bcc'  => array('admin_email', 'form_*', 'formconfig_*'),
                 'email_replyTo'        => array('admin_email', 'form_*', 'formconfig_*'),
                 'attachment_tokens'    => array('form_*', 'formconfig_*'),
             ),
             'member_activation' => array(
                 'recipients'           => array('member_email', 'admin_email'),
                 'email_subject'        => array('domain', 'member_*', 'admin_email'),
                 'email_text'           => array('domain', 'member_*', 'admin_email'),
                 'email_html'           => array('domain', 'member_*', 'admin_email'),
                 'file_name'            => array('domain', 'member_*', 'admin_email'),
                 'file_content'         => array('domain', 'member_*', 'admin_email'),
                 'email_sender_name'    => array('admin_email', 'form_*'),
                 'email_sender_address' => array('admin_email', 'form_*'),
                 'email_recipient_cc'   => array('admin_email', 'member_*'),
                 'email_recipient_bcc'  => array('admin_email', 'member_*'),
                 'email_replyTo'        => array('admin_email', 'member_*'),
             ),
             'member_registration' => array(
                 'recipients'           => array('member_email', 'admin_email'),
                 'email_subject'        => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_text'           => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_html'           => array('domain', 'link', 'member_*', 'admin_email'),
                 'file_name'            => array('domain', 'link', 'member_*', 'admin_email'),
                 'file_content'         => array('domain', 'link', 'member_*', 'admin_email'),
                 'email_sender_name'    => array('admin_email', 'member_*'),
                 'email_sender_address' => array('admin_email', 'member_*'),
                 'email_recipient_cc'   => array('admin_email', 'member_*'),
                 'email_recipient_bcc'  => array('admin_email', 'member_*'),
                 'email_replyTo'        => array('admin_email', 'member_*'),
             ),
             'member_personaldata' => array(
                 'recipients'           => array('member_email', 'admin_email'),
                 'email_subject'        => array('domain', 'member_*', 'member_old_*', 'changed_*', 'admin_email'),
                 'email_text'           => array('domain', 'member_*', 'member_old_*', 'changed_*', 'admin_email'),
                 'email_html'           => array('domain', 'member_*', 'member_old_*', 'changed_*', 'admin_email'),
                 'email_sender_name'    => array('member_*'),
                 'email_sender_address' => array('member_email', 'admin_email'),
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
                 'email_sender_name'    => array('recipient_email'),
                 'email_sender_address' => array('recipient_email'),
                 'email_recipient_cc'   => array('recipient_email'),
                 'email_recipient_bcc'  => array('recipient_email'),
                 'email_replyTo'        => array('recipient_email'),
             ),
         )
    )
);

// Add the newsletter tokens only if the extension is active
if ((version_compare(VERSION, '4.0', '>=') && in_array('Contao\NewsletterBundle\ContaoNewsletterBundle', \Contao\System::getContainer()->getParameter('kernel.bundles'), true))
    || (version_compare(VERSION, '4.0', '<') && in_array('newsletter', \Contao\ModuleLoader::getActive(), true))
) {
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['newsletter_subscribe'] = array(
        'recipients'           => array('recipient_email', 'admin_email'),
        'email_subject'        => array('domain', 'link', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'subject'),
        'email_text'           => array('domain', 'link', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'token'),
        'email_html'           => array('domain', 'link', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'token'),
        'file_name'            => array('domain', 'link', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'file_content'         => array('domain', 'link', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'token'),
        'email_sender_name'    => array('recipient_email', 'admin_email', 'admin_name'),
        'email_sender_address' => array('recipient_email', 'admin_email'),
        'email_recipient_cc'   => array('recipient_email', 'admin_email'),
        'email_recipient_bcc'  => array('recipient_email', 'admin_email'),
        'email_replyTo'        => array('recipient_email', 'admin_email'),
    );

    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['newsletter_activate'] = array(
        'recipients'           => array('recipient_email', 'admin_email'),
        'email_subject'        => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'subject'),
        'email_text'           => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'email_html'           => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'file_name'            => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'file_content'         => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'email_sender_name'    => array('recipient_email', 'admin_email', 'admin_name'),
        'email_sender_address' => array('recipient_email', 'admin_email'),
        'email_recipient_cc'   => array('recipient_email', 'admin_email'),
        'email_recipient_bcc'  => array('recipient_email', 'admin_email'),
        'email_replyTo'        => array('recipient_email', 'admin_email'),
    );

    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['newsletter_unsubscribe'] = array(
        'recipients'           => array('recipient_email', 'admin_email'),
        'email_subject'        => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids', 'subject'),
        'email_text'           => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'email_html'           => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'file_name'            => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'file_content'         => array('domain', 'recipient_email', 'admin_email', 'channels', 'channel_ids'),
        'email_sender_name'    => array('recipient_email', 'admin_email', 'admin_name'),
        'email_sender_address' => array('recipient_email', 'admin_email'),
        'email_recipient_cc'   => array('recipient_email', 'admin_email'),
        'email_recipient_bcc'  => array('recipient_email', 'admin_email'),
        'email_replyTo'        => array('recipient_email', 'admin_email'),
    );
}
