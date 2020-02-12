<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

if (\Database::getInstance()->tableExists('tl_nc_language') && \Database::getInstance()->fieldExists('file_override', 'tl_nc_language')) {
    \Database::getInstance()->execute("ALTER TABLE tl_nc_language CHANGE file_override file_storage_mode varchar(8) NOT NULL default ''");
    \Database::getInstance()->execute("UPDATE tl_nc_language SET file_storage_mode='override' WHERE file_storage_mode!=''");
}
