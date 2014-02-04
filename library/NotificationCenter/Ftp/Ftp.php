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

namespace NotificationCenter\Ftp;


class Ftp implements FtpInterface
{
    /**
     * Is connected
     * @var boolean
     */
    protected $blnConnected = false;

    /**
     * Connection
     * @var resource
     */
    protected $resConnection;

    /**
     * Secure connection
     * @var boolean
     */
    protected $blnSecure = false;

    /**
     * Path on the server
     * @var string
     */
    protected $strPath = '';

    /**
     * Disconnect from FTP server
     */
    public function __destruct()
    {
        if ($this->blnConnected) {
            @ftp_close($this->resConnection);
        }
    }

    /**
     * Connect to the FTP server
     * @param object
     * @throws \Exception
     */
    public function connect($objGateway)
    {
        if ($this->blnConnected) {
            return;
        }

        $strFunction = ($this->blnSecure && function_exists('ftp_ssl_connect')) ? 'ftp_ssl_connect' : 'ftp_connect';

        // Try to connect
        if (($this->resConnection = $strFunction($objGateway->ftp_host, intval($objGateway->ftp_port), 5)) === false) {
            throw new \Exception('Could not connect to the FTP server');
        }
        // Try to login
        elseif (@ftp_login($this->resConnection, $objGateway->ftp_username, $objGateway->ftp_password) === false) {
            throw new \Exception('Authentication failed');
        }

        // Switch to passive mode
        ftp_pasv($this->resConnection, true);

        $this->blnConnected = true;
        $this->strPath = $objGateway->ftp_path;
    }

    /**
     * Save the file
     * @param string
     * @param string
     * @param boolean
     * @return boolean
     */
    public function save($strFile, $strContent, $blnOverride=false)
    {
        $objFile = new \File('system/tmp/' . md5(uniqid(mt_rand(), true)));
        $objFile->write($strContent);
        $objFile->close();

        // Do not override the file
        if (!$blnOverride) {
            $arrAll = @ftp_nlist($this->resConnection, $this->strPath);

            if ($arrAll === false) {
                return false;
            }

            // Remove the predefined path from files
            foreach ($arrAll as $k => $v) {
                $arrAll[$k] = str_replace($this->strPath . '/', '', $v);
            }

            if (in_array($strFile, $arrAll)) {
                $offset = 1;
                $pathinfo = pathinfo($strFile);
                $name = $pathinfo['filename'];
                $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrAll);

                foreach ($arrFiles as $file) {
                    if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file)) {
                        $file = str_replace('.' . $pathinfo['extension'], '', $file);
                        $intValue = intval(substr($file, (strrpos($file, '_') + 1)));

                        $offset = max($offset, $intValue);
                    }
                }

                $strFile = str_replace($name, $name . '__' . ++$offset, $strFile);
            }
        }

        // Copy the temporary file
        $blnResult = @ftp_fput($this->resConnection, $this->strPath . '/' . $strFile, $objFile->handle, FTP_BINARY);

        // Delete temporary file
        $objFile->delete();

        return $blnResult;
    }
}