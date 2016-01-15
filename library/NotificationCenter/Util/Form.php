<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */


namespace NotificationCenter\Util;


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
        if (!is_uploaded_file($file['tmp_name'])) {

            if(file_exists($file['tmp_name'])) {
                $basePath = TL_ROOT . "/";
                return str_replace($basePath, '', $file['tmp_name'] );
            }
            return null;
        }

        $tmpDir   = 'system/tmp';
        $filePath = $tmpDir . '/' . $file['name'];

        \Files::getInstance()->move_uploaded_file($file['tmp_name'], $filePath);
        \Files::getInstance()->chmod($filePath, $GLOBALS['TL_CONFIG']['defaultFileChmod']);


        return $filePath;
    }
}
