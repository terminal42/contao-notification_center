<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

/**
 * Tokens
 */
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['admin_email'] = 'E-mail address of administrator of the current page.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['form_*']      = 'All the form fields.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['formlabel_*'] = 'All the form field labels.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['raw_data']    = 'All the form fields and their raw values.';

$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['admin_email']  = 'E-mail address of administrator of the current page.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['recipients']   = 'E-mail address of the member.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['domain']       = 'The current domain.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['member_*']     = 'Current member fields as submitted by the form. Use {{user::*}} insert tags if you need other properties.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['member_old_*'] = 'Member fields as they were before the changes.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['member_personaldata']['changed_*']    = 'Flag (1 or 0) if a field has changed, to be used with if-conditions.';
