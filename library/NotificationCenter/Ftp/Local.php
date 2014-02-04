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

class Local implements FtpInterface
{
    /**
     * Destination path
     * @var string
     */
    protected $strPath = '';

    /**
     * Set the path
     * @param object
     */
    public function connect($objGateway)
    {
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
        if (!$blnOverride && is_file(TL_ROOT . '/' . $this->strPath . '/' . $strFile)) {
			$offset = 1;
			$pathinfo = pathinfo($strFile);
			$name = $pathinfo['filename'];

			$arrAll = scan(TL_ROOT . '/' . $this->strPath);
			$arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrAll);

			foreach ($arrFiles as $file)
			{
				if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file))
				{
					$file = str_replace('.' . $pathinfo['extension'], '', $file);
					$intValue = intval(substr($file, (strrpos($file, '_') + 1)));

					$offset = max($offset, $intValue);
				}
			}

			$strFile = str_replace($name, $name . '__' . ++$offset, $strFile);
        }

        $objFile = new \File($this->strPath . '/' . $strFile);
        $blnResult = $objFile->write($strContent);
        $objFile->close();

        return $blnResult;
    }
}