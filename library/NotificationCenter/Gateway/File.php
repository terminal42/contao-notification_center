<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Gateway;

use Codefog\HasteBundle\StringParser;
use Contao\Config;
use Contao\Folder;
use Contao\System;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;
use NotificationCenter\Util\StringUtil;


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
     * @param Message $objMessage
     * @param array   $arrTokens
     * @param string  $strLanguage
     *
     * @return bool
     */
    public function send(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ($strLanguage == '') {
            $strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (($objLanguage = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage)) === null) {
            System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);

            return false;
        }

        $strFileName = System::getContainer()->get(StringParser::class)->recursiveReplaceTokensAndTags(
            $objLanguage->file_name,
            $arrTokens,
            StringUtil::NO_TAGS | StringUtil::NO_BREAKS
        );

        // Escape quotes and line breaks for CSV files
        if ('csv' === $this->objModel->file_type) {
            array_walk($arrTokens, function (&$varValue) {
                $varValue = str_replace(array('"', "\r\n", "\r"), array('""', "\n", "\n"), $varValue);
            });
        }

        // Preserve all tags here as this is pretty useful in XML :-)
        $strContent = System::getContainer()->get(StringParser::class)->recursiveReplaceTokensAndTags(
            $objLanguage->file_content,
            $arrTokens
        );

        try {
            return $this->save($strFileName, $strContent, (string) $objLanguage->file_storage_mode);
        } catch (\Exception $e) {
            System::log('Notification Center gateway error: ' . $e->getMessage(), __METHOD__, TL_ERROR);

            return false;
        }

    }

    /**
     * Test connection to the given server
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    public function testConnection()
    {
        if (in_array($this->objModel->file_connection, ['local', 'ftp'], true)) {
            return true;
        }

        throw new \UnexpectedValueException(
            sprintf('Unknown server connection of type "%s"', $this->objModel->file_connection)
        );
    }

    /**
     * Save file to the server
     *
     * @param string $strFileName
     * @param string $strContent
     * @param string $strStorageMode
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    protected function save($strFileName, $strContent, $strStorageMode)
    {
        switch ($this->objModel->file_connection) {

            case 'local':
                return $this->saveToLocal($strFileName, $strContent, $strStorageMode);

            case 'ftp':
                return $this->saveToFTP($strFileName, $strContent, $strStorageMode);
        }

        throw new \UnexpectedValueException(
            sprintf('Unknown server connection of type "%s"', $this->objModel->file_connection)
        );
    }

    /**
     * Generate a unique filename based on folder content
     *
     * @param string $strFile
     * @param array  $arrFiles
     *
     * @return string
     */
    protected function getUniqueFileName($strFile, $arrFiles)
    {
        if (!in_array($strFile, $arrFiles)) {
            return $strFile;
        }

        $offset   = 0;
        $pathinfo = pathinfo($strFile);
        $name     = $pathinfo['filename'];

        // Look for file that start with same name and have same file extension
        $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrFiles);

        foreach ($arrFiles as $file) {
            if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file)) {
                $file     = str_replace('.' . $pathinfo['extension'], '', $file);
                $intValue = (int) substr($file, (strrpos($file, '__') + 2));

                $offset = max($offset, $intValue);
            }
        }

        return str_replace($name, $name . '__' . ++$offset, $strFile);
    }

    /**
     * Save file to local server
     *
     * @param string $strFileName
     * @param string $strContent
     * @param string $strStorageMode
     *
     * @return bool
     */
    protected function saveToLocal($strFileName, $strContent, $strStorageMode)
    {
        // Make sure the directory exists
        if (!is_dir(sprintf('%s/%s', TL_ROOT, $this->objModel->file_path))) {
            $folder = new Folder($this->objModel->file_path);

            if (Config::get('defaultFolderChmod')) {
                $folder->chmod(Config::get('defaultFolderChmod'));
            }
        }

        // Make sure we don't overwrite existing files
        if ($strStorageMode === self::FILE_STORAGE_CREATE
            && is_file(TL_ROOT . '/' . $this->objModel->file_path . '/' . $strFileName)
        ) {
            $strFileName = $this->getUniqueFileName($strFileName, Folder::scan(TL_ROOT . '/' . $this->objModel->file_path, true));
        }

        $objFile = new \Contao\File($this->objModel->file_path . '/' . $strFileName);

        // Don't start with a newline
        if ($strStorageMode === self::FILE_STORAGE_APPEND
            && $objFile->exists()
            && $objFile->getContent() !== ''
        ) {
            $strContent = $objFile->getContent() . "\n" . $strContent;
        }

        $blnResult = $objFile->write($strContent);
        $objFile->close();
        $objFile->chmod(Config::get('defaultFileChmod'));

        return $blnResult;
    }

    /**
     * Save file to FTP server
     *
     * @param string $strFileName
     * @param string $strContent
     * @param string $strStorageMode
     *
     * q@return bool
     *
     * @throws \RuntimeException
     */
    protected function saveToFTP($strFileName, $strContent, $strStorageMode)
    {
        if (($resConnection = ftp_connect($this->objModel->file_host, (int) ($this->objModel->file_port ?: 21), 5)) === false) {
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

            // Don't start with a newline
            if ($fileContents !== '') {
                $strContent .= $fileContents . "\n" . $strContent;
            }
        }

        // Write content to temporary file
        $objFile = new \Contao\File(sys_get_temp_dir() . '/' . md5(uniqid(mt_rand(), true)));
        $objFile->write($strContent);
        $objFile->close();

        // Copy the temporary file to the server
        $blnResult = @ftp_put($resConnection, $this->objModel->file_path . '/' . $strFileName, TL_ROOT . '/' . $objFile->path, FTP_BINARY);

        // Delete temporary file and close FTP connection
        $objFile->delete();
        @ftp_chmod($resConnection, Config::get('defaultFileChmod'), $this->objModel->file_path . '/' . $strFileName);
        @ftp_close($resConnection);

        return $blnResult;
    }
}
