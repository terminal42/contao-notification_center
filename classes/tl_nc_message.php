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

namespace NotificationCenter;

class tl_nc_message extends \Backend
{
    /**
     * Available translations
     * @var array
     */
    protected $arrTranslations = array();

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
        if (empty($this->arrTranslations)) {
            $this->arrTranslations = \System::getLanguages();
        }
        if (empty($this->arrGateways)) {
            $objGateways = \Database::getInstance()->execute('SELECT id,title FROM tl_nc_gateway');
            while ($objGateways->next()) {
                $this->arrGateways[$objGateways->id] = $objGateways->title;
            }
        }

        $arrTranslations =  \Database::getInstance()->prepare('SELECT language FROM tl_nc_language WHERE pid=?')->execute($arrRow['id'])->fetchEach('language');

        $strBuffer = '
<div class="cte_type ' . (($arrRow['published']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['title'] . '</strong> - ' . $this->arrGateways[$arrRow['gateway']] . '</div>
<div>
<ul>';

        foreach ($arrTranslations as $strLang) {
            $strBuffer .= '<li>&#10148; ' . $this->arrTranslations[$strLang] . '</li>';
        }

        $strBuffer .= '</ul></div>';

        return $strBuffer;
    }

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));

            if (\Environment::get('isAjaxRequest')) {
                exit;
            }

            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        $objVersions = new \Versions('tl_nc_message', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_nc_message']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_nc_message']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = \System::importStatic($callback[0])->$callback[1]($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_nc_message SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_nc_message.id=' . $intId . '" has been created', __METHOD__, TL_GENERAL);
    }
}