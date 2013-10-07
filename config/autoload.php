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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('NotificationCenter', 'system/modules/notification_center/library');


/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(
    // DCA Helpers
    'NotificationCenter\tl_module'              => 'system/modules/notification_center/classes/tl_module.php',
    'NotificationCenter\tl_nc_bag'              => 'system/modules/notification_center/classes/tl_nc_bag.php',
    'NotificationCenter\tl_nc_language'         => 'system/modules/notification_center/classes/tl_nc_language.php',
    'NotificationCenter\tl_nc_message'          => 'system/modules/notification_center/classes/tl_nc_message.php'
));