<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
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
     * File storage options
     */
    const FILE_STORAGE_CREATE   = ''; // Creates a new file every time
    const FILE_STORAGE_OVERRIDE = 'override'; // Overrides the existing file if available
    const FILE_STORAGE_APPEND   = 'append'; // Appends to an existing file if available

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
            return $this->save($strFileName, $strContent, (string) $objLanguage->file_storage_mode);
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
     * @param   string
     * @return  bool
     * @throws  \UnexpectedValueException
     */
    protected function save($strFileName, $strContent, $strStorageMode)
    {
        switch ($this->objModel->file_connection) {

            case 'local':
                return $this->saveToLocal($strFileName, $strContent, $strStorageMode);

            case 'ftp':
                return $this->saveToFTP($strFileName, $strContent, $strStorageMode);

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
     * @param   string
     * @return  bool
     */
    protected function saveToLocal($strFileName, $strContent, $strStorageMode)
    {
        // Make sure the directory exists
        if (!is_dir(TL_ROOT . '/' . $this->objModel->file_path)) {
            new \Folder($this->objModel->file_path);
        }

        // Make sure we don't overwrite existing files
        if ($strStorageMode === self::FILE_STORAGE_CREATE
            && is_file(TL_ROOT . '/' . $this->objModel->file_path . '/' . $strFileName)
        ) {
            $strFileName = $this->getUniqueFileName($strFileName, scan(TL_ROOT . '/' . $this->objModel->file_path, true));
        }

        $objFile = new \File($this->objModel->file_path . '/' . $strFileName);

        if ($strStorageMode === self::FILE_STORAGE_APPEND) {
            $strContent = $objFile->getContent() . "\n" . $strContent;
        }

        $blnResult = $objFile->write($strContent);
        $objFile->close();

        return $blnResult;
    }

    /**
     * Save file to FTP server
     *
     * @param   string
     * @param   string
     * @param   string
     * @return  bool
     * @throws  \RuntimeException
     */
    protected function saveToFTP($strFileName, $strContent, $strStorageMode)
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
        if ($strStorageMode === self::FILE_STORAGE_CREATE) {
            if (($arrFiles = @ftp_nlist($resConnection, $this->objModel->file_path)) === false) {
                @ftp_close($resConnection);
                return false;
            }

            $strFileName = $this->getUniqueFileName($strFileName, $arrFiles);
        }

        if ($strStorageMode === self::FILE_STORAGE_APPEND) {
            ob_start();
            ftp_get($resConnection, "php://output", $this->objModel->file_path, FTP_BINARY);
            $fileContents = ob_get_contents();
            ob_end_clean();

            $strContent .= $fileContents . "\n" . $strContent;
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
