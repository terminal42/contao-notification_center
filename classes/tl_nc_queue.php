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
}