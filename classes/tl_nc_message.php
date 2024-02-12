<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;

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
     * Modifies the palette for the queue gateway so it takes the one from the target gateway
     *
     * @param \DataContainer $dc
     */
    public function modifyPalette(\DataContainer $dc)
    {
        if ('edit' !== \Input::get('act') || !$dc->id || ($message = Message::findByPk($dc->id)) === null) {
            return;
        }

        $gateway = $message->getRelated('gateway');

        if ($gateway !== null && 'queue' === $gateway->type) {
            $targetGateway = Gateway::findByPk($gateway->queue_targetGateway);
            $GLOBALS['TL_DCA']['tl_nc_message']['palettes']['queue'] =
                $GLOBALS['TL_DCA']['tl_nc_message']['palettes'][$targetGateway->type];
        }
    }

    /**
     * child_record_callback
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listRows($arrRow)
    {
        if (0 === count($this->arrTranslations)) {
            $this->arrTranslations = \System::getLanguages();
        }
        if (0 === count($this->arrGateways)) {
            $objGateways = \Database::getInstance()->execute('SELECT id,title FROM tl_nc_gateway');
            while ($objGateways->next()) {
                $this->arrGateways[$objGateways->id] = $objGateways->title;
            }
        }

        $arrTranslations =  \Database::getInstance()->prepare('SELECT language FROM tl_nc_language WHERE pid=?')->execute($arrRow['id'])->fetchEach('language');

        $strBuffer = '
<div class="cte_type ' . (($arrRow['published']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['title'] . '</strong> - ' . ($this->arrGateways[$arrRow['gateway']] ?? $arrRow['gateway']) . '</div>
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
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (!empty(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));

            if (\Environment::get('isAjaxRequest')) {
                exit;
            }

            \Controller::redirect(\System::getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . \Backend::addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
        if (is_array($GLOBALS['TL_DCA']['tl_nc_message']['fields']['published']['save_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA']['tl_nc_message']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = \System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_nc_message SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
        \System::log('A new version of record "tl_nc_message.id=' . $intId . '" has been created', __METHOD__, TL_GENERAL);
    }
}
