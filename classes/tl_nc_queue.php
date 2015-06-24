<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use NotificationCenter\Model\QueuedMessage;

class tl_nc_queue extends \Backend
{
    /**
     * label_callback
     * @param   array
     * @return  string
     */
    public function listRows($arrRow, $label, $dc)
    {
        $strBuffer = '<span style="color:#b3b3b3;padding-right:3px">[%s]</span>';
        $arrValues = array(\Date::parse(\Date::getNumericDatimFormat(), $arrRow['dateAdded']));

        $objQueuedMessage = QueuedMessage::findByPk($arrRow['id']);

        $arrStatusColorClasses = array(
            'queued'    => 'tl_orange',
            'sent'      => 'tl_green',
            'error'     => 'tl_red'
        );

        $strBuffer .= ' <span class="%s">%s</span>';
        $arrValues[] = $arrStatusColorClasses[$objQueuedMessage->getStatus()];
        $arrValues[] = &$GLOBALS['TL_LANG']['tl_nc_queue']['status'][$objQueuedMessage->getStatus()];

        if (($objMessage = $objQueuedMessage->getRelated('message')) !== null) {
            $strBuffer .= ' <div class="tl_gray">%s: %s <a href="%s" class="tl_gray">[%s]</a></div>';
            $arrValues[] = $GLOBALS['TL_LANG']['tl_nc_queue']['source'];
            $arrValues[] = $objMessage->title;
            $arrValues[] =
                sprintf('contao/main.php?do=nc_notifications&table=tl_nc_message&act=edit&id=%s&rt=%s&ref=9b33cf83',
                    $objMessage->id,
                    REQUEST_TOKEN,
                    TL_REFERER_ID);
            $arrValues[] = $objMessage->id;

        }

        return vsprintf($strBuffer, $arrValues);
    }

    /**
     * Re-queue a queued message
     * @param   \DataContainer
     */
    public function reQueue(\DataContainer $dc)
    {
        $objQueuedMsg = QueuedMessage::findByPk($dc->id);
        $objQueuedMsg->reQueue();
        \Controller::redirect(str_replace('&key=re-queue', '', \Environment::get('request')));
    }

    /**
     * Return the re-queue button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function reQueueButton($row, $href, $label, $title, $icon, $attributes)
    {
        $objMessage = QueuedMessage::findByPk($row['id']);
        return ($objMessage->getStatus() === 'error') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : '';
    }

    /**
     * Return the delete button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        $objMessage = QueuedMessage::findByPk($row['id']);
        return ($objMessage->getStatus() !== 'sent') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : '';
    }
}
