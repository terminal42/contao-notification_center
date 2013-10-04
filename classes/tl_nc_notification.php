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

namespace NotificationCenter;

class tl_nc_notification extends \Backend
{
    /**
     * Available languages
     * @var array
     */
    protected $arrLanguages = array();

    /**
     * Available gateways
     * @var array
     */
    protected $arrGateways = array();

    /**
     * child_record_callback
     * @param   array
     * @return  string
     */
    public function listRows($arrRow)
    {
        if (empty($this->arrLanguages)) {
            $this->arrLanguages = \System::getLanguages();
        }
        if (empty($this->arrGateways)) {
            $objGateways = \Database::getInstance()->execute('SELECT id,title FROM tl_nc_gateway');
            while ($objGateways->next()) {
                $this->arrGateways[$objGateways->id] = $objGateways->title;
            }
        }

        $arrLanguages = array();

        $objLanguages = \Database::getInstance()->prepare('SELECT language FROM tl_nc_language WHERE pid=?')->execute($arrRow['id']);
        while ($objLanguages->next()) {
            $arrLanguages[] = $this->arrLanguages[$objLanguages->language];
        }

        // @todo style this a little nicer
        return '
<div class="cte_type">
<p><strong>' . $arrRow['title'] . '</strong> - ' . $this->arrGateways[$arrRow['gateway']] . '</p>
<p>' . implode(', ', $arrLanguages) . '</p>
</div>';
    }
}