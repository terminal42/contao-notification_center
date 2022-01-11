<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */


namespace NotificationCenter\Util;


use Contao\Validator;
use Symfony\Component\Filesystem\Filesystem;

class Form
{
    /**
     * Moves an uploaded file to the tmp folder and returns its TL_ROOT relative path.
     * If it was not properly uploaded, the method will return null.
     *
     * @param array $file
     * @return null|string
     */
    public static function getFileUploadPathForToken(array $file)
    {
        // Check if it has been saved by Contao and thus moved to its final destination already
        if (isset($file['uploaded']) && $file['uploaded'] === true) {
            if (file_exists($file['tmp_name'])) {
                return $file['tmp_name'];
            }

            return null;
        }

        if (!is_uploaded_file($file['tmp_name'])) {

            return null;
        }

        if (Validator::isInsecurePath($file['tmp_name'])) {

            return null;
        }

        $filePath = sys_get_temp_dir() . '/' . $file['name'];
        move_uploaded_file($file['tmp_name'], $filePath);
        (new Filesystem())->chmod($filePath,$GLOBALS['TL_CONFIG']['defaultFileChmod']);

        return $filePath;
    }
}
