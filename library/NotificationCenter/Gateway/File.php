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

namespace NotificationCenter\Gateway;

use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;
use Haste\Haste;
use NotificationCenter\Util\String;


class File extends Base implements GatewayInterface
{

    /**
     * Create file
     *
     * @param   Message
     * @param   array
     * @param   string
     * @return  bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);
            return false;
        }

        $strFileName = String::recursiveReplaceTokensAndTags(
            $objLanguage->file_name,
            $arrTokens,
            String::NO_TAGS|String::NO_BREAKS
        );

        // Escape quotes and line breaks for CSV files
        if ($this->objModel->file_type == 'csv') {
            array_walk($arrTokens, function(&$varValue) {
                $varValue = str_replace(array('"', "\r\n", "\r"), array('""', "\n", "\n"), $varValue);
            });
        }

        // Preserve all tags here as this is pretty useful in XML :-)
        $strContent = String::recursiveReplaceTokensAndTags(
            $objLanguage->file_content,
            $arrTokens
        );

        try {
            return $this->save($strFileName, $strContent, (bool) $objLanguage->file_override);
        } catch (\Exception $e) {
            \System::log('Notification Center gateway error: ' . $e->getMessage(), __METHOD__, TL_ERROR);
            return false;
        }

    }

    /**
     * Test connection to the given server
     *
     * @return  bool
     */
    public function testConnection()
    {
        switch ($this->objModel->file_connection) {

            case 'local':
                break;

            case 'ftp':
                break;

            default:
                throw new \UnexpectedValueException('Unknown server connection of type "' . $this->objModel->file_connection . '"');
        }
    }

    /**
     * Save file to the server
     *
     * @param   string
     * @param   string
     * @param   bool
     * @return  bool
     * @throws  \UnexpectedValueException
     */
    protected function save($strFileName, $strContent, $blnOverride)
    {
        switch ($this->objModel->file_connection) {

            case 'local':
                return $this->saveToLocal($strFileName, $strContent, $blnOverride);

            case 'ftp':
                return $this->saveToFTP($strFileName, $strContent, $blnOverride);

            default:
                throw new \UnexpectedValueException('Unknown server connection of type "' . $this->objModel->file_connection . '"');
        }
    }

    /**
     * Generate a unique filename based on folder content
     *
     * @param   string
     * @param   array
     * @return  string
     */
    protected function getUniqueFileName($strFile, $arrFiles)
    {
        if (!in_array($strFile, $arrFiles)) {
            return $strFile;
        }

        $offset = 0;
        $pathinfo = pathinfo($strFile);
        $name = $pathinfo['filename'];

        // Look for file that start with same name and have same file extension
        $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrFiles);

        foreach ($arrFiles as $file) {
            if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file)) {
                $file = str_replace('.' . $pathinfo['extension'], '', $file);
                $intValue = intval(substr($file, (strrpos($file, '__') + 2)));

                $offset = max($offset, $intValue);
            }
        }

        return str_replace($name, $name . '__' . ++$offset, $strFile);
    }

    /**
     * Save file to local server
     *
     * @param   string
     * @param   string
     * @param   bool
     * @return  bool
     */
    protected function saveToLocal($strFileName, $strContent, $blnOverride)
    {
        // Make sure the directory exists
        if (!is_dir(TL_ROOT . '/' . $this->objModel->file_path)) {
            new \Folder($this->objModel->file_path);
        }

        // Make sure we don't overwrite existing files
        if (!$blnOverride && is_file(TL_ROOT . '/' . $this->objModel->file_path . '/' . $strFileName)) {
            $strFileName = $this->getUniqueFileName($strFileName, scan(TL_ROOT . '/' . $this->objModel->file_path, true));
        }

        $objFile = new \File($this->objModel->file_path . '/' . $strFileName);
        $blnResult = $objFile->write($strContent);
        $objFile->close();

        return $blnResult;
    }

    /**
     * Save file to FTP server
     *
     * @param   string
     * @param   string
     * @param   bool
     * @return  bool
     * @throws  \RuntimeException
     */
    protected function saveToFTP($strFileName, $strContent, $blnOverride)
    {
        if (($resConnection = ftp_connect($this->objModel->file_host, intval($this->objModel->file_port ?: 21), 5)) === false) {
            throw new \RuntimeException('Could not connect to the FTP server');
        }

        if (@ftp_login($resConnection, $this->objModel->file_username, $this->objModel->file_password) === false) {
            @ftp_close($resConnection);
            throw new \RuntimeException('FTP server authentication failed');
        }

        // @todo should be configurable
        ftp_pasv($resConnection, true);

        // Make sure we don't overwrite existing files
        if (!$blnOverride) {
            if (($arrFiles = @ftp_nlist($resConnection, $this->objModel->file_path)) === false) {
                @ftp_close($resConnection);
                return false;
            }

            $strFileName = $this->getUniqueFileName($strFileName, $arrFiles);
        }

        // Write content to temporary file
        $objFile = new \File('system/tmp/' . md5(uniqid(mt_rand(), true)));
        $objFile->write($strContent);
        $objFile->close();

        // Copy the temporary file to the server
        $blnResult = @ftp_put($resConnection, $this->objModel->file_path . '/' . $strFileName, $objFile->path, FTP_BINARY);

        // Delete temporary file and close FTP connection
        $objFile->delete();
        @ftp_close($resConnection);

        return $blnResult;
    }
}
