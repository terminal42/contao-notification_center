<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Util;

use Haste\Haste;

if (version_compare(PHP_VERSION, '7.0', '>=')) {
    throw new \RuntimeException(
        'The String class cannot be used in PHP ' . PHP_VERSION . '. Use the StringUtil class instead.'
    );
}

/**
 * @deprecated Use the StringUtil class instead
 */
class String extends StringUtil
{
} 
