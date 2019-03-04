<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
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
 * @author     Kamil Kuzminski <kamil.kuzminski@gmail.com>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Contao;


class ModulePasswordNotificationCenter extends \ModulePassword
{

	/**
	 * Send a lost password e-mail
	 * @param object
	 */
	protected function sendPasswordLink($objMember)
	{
		$objNotification = \NotificationCenter\Model\Notification::findByPk($this->nc_notification);

		if ($objNotification === null) {
			$this->log('The notification was not found ID ' . $this->nc_notification, __METHOD__, TL_ERROR);
			return;
		}

		$token = md5(uniqid(mt_rand(), true));
		$contaoVersion = VERSION.'.'.BUILD;
		if (version_compare($contaoVersion, '4.7.0', '>=')) {
			/** @var \Contao\CoreBundle\OptIn\OptIn $optIn */
			$optIn = System::getContainer()->get('contao.opt-in');
			$optInToken = $optIn->create('pw-', $objMember->email, array('tl_member'=>array($objMember->id)));
			$token = $optInToken->getIdentifier();
		} elseif (version_compare($contaoVersion, '4.4.12', '>=')) {
			$token = 'PW' . substr($token, 2);
		}

		if (!version_compare($contaoVersion, '4.7.0', '>=')) {
			// Store the token
			$objMember = \MemberModel::findByPk($objMember->id);
			$objMember->activation = $token;
			$objMember->save();
		}

		$arrTokens = array();

		// Add member tokens
		foreach ($objMember->row() as $k => $v)
		{
			$arrTokens['member_' . $k] = $v;
		}

		$arrTokens['recipient_email'] = $objMember->email;
		$arrTokens['domain'] = \Idna::decode(\Environment::get('host'));
		$arrTokens['link'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $token;

		$objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
		$this->log('A new password has been requested for user ID ' . $objMember->id . ' (' . $objMember->email . ')', __METHOD__, TL_ACCESS);

		// Check whether there is a jumpTo page
		if (($objJumpTo = $this->objModel->getRelated('jumpTo')) !== null)
		{
			$this->jumpToOrReload($objJumpTo->row());
		}

		$this->reload();
	}
}
