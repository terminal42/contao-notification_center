<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Front end module "newsletter unsubscribe".
 *
 * @property bool   $nl_hideChannels
 * @property string $nl_unsubscribe
 * @property array  $nl_channels
 * @property string $nl_template
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleNewsletterUnsubscribeNotificationCenter extends ModuleUnsubscribe
{
	/**
	 * Generate the module
	 */
	protected function compile()
	{
		// Overwrite default template
		if ($this->nl_template)
		{
			$this->Template = new \FrontendTemplate($this->nl_template);
			$this->Template->setData($this->arrData);
		}

		$this->Template->email = '';
		$this->Template->captcha = '';

		$objWidget = null;

		// Set up the captcha widget
		if (!$this->disableCaptcha)
		{
			$arrField = array
			(
				'name' => 'unsubscribe_'.$this->id,
				'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
				'inputType' => 'captcha',
				'eval' => array('mandatory'=>true)
			);

			/** @var Widget $objWidget */
			$objWidget = new \FormCaptcha(\FormCaptcha::getAttributesFromDca($arrField, $arrField['name']));
		}

		$strFormId = 'tl_unsubscribe_' . $this->id;

		// Unsubscribe
		if (\Input::post('FORM_SUBMIT') == $strFormId)
		{
			$varSubmitted = $this->validateForm($objWidget);

			if ($varSubmitted !== false)
			{
				\call_user_func_array(array($this, 'removeRecipient'), $varSubmitted);
			}
		}

		// Add the captcha widget to the template
		if ($objWidget !== null)
		{
			$this->Template->captcha = $objWidget->parse();
		}

		$session = \System::getContainer()->get('session');
		$flashBag = $session->getFlashBag();

		// Confirmation message
		if ($session->isStarted() && $flashBag->has('nl_removed'))
		{
			$arrMessages = $flashBag->get('nl_removed');

			$this->Template->mclass = 'confirm';
			$this->Template->message = $arrMessages[0];
		}

		$arrChannels = array();
		$objChannel = \NewsletterChannelModel::findByIds($this->nl_channels);

		// Get the titles
		if ($objChannel !== null)
		{
			while ($objChannel->next())
			{
				$arrChannels[$objChannel->id] = $objChannel->title;
			}
		}

		// Default template variables
		$this->Template->channels = $arrChannels;
		$this->Template->showChannels = !$this->nl_hideChannels;
		$this->Template->email = \Input::get('email');
		$this->Template->submit = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['unsubscribe']);
		$this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
		$this->Template->emailLabel = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
		$this->Template->action = \Environment::get('indexFreeRequest');
		$this->Template->formId = $strFormId;
		$this->Template->id = $this->id;
	}

	/**
	 * Remove the recipient
	 *
	 * @param string $strEmail
	 * @param array  $arrRemove
	 */
	protected function removeRecipient($strEmail, $arrRemove)
	{
		// Remove the subscriptions
		if (($objRemove = \NewsletterRecipientsModel::findByEmailAndPids($strEmail, $arrRemove)) !== null)
		{
			while ($objRemove->next())
			{
				$strHash = md5($objRemove->email);

				// Add a blacklist entry (see #4999)
				if (($objBlacklist = \NewsletterBlacklistModel::findByHashAndPid($strHash, $objRemove->pid)) === null)
				{
					$objBlacklist = new \NewsletterBlacklistModel();
					$objBlacklist->pid = $objRemove->pid;
					$objBlacklist->hash = $strHash;
					$objBlacklist->save();
				}

				$objRemove->delete();
			}
		}

		// Get the channels
		$objChannels = \NewsletterChannelModel::findByIds($arrRemove);
		$arrChannels = $objChannels->fetchEach('title');

		// HOOK: post unsubscribe callback
		if (isset($GLOBALS['TL_HOOKS']['removeRecipient']) && \is_array($GLOBALS['TL_HOOKS']['removeRecipient']))
		{
			foreach ($GLOBALS['TL_HOOKS']['removeRecipient'] as $callback)
			{
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($strEmail, $arrRemove);
			}
		}

		// Prepare the simple token data
		$arrData = array();
		$arrData['domain'] = \Idna::decode(\Environment::get('host'));
		$arrData['channel'] = $arrData['channels'] = implode("\n", $arrChannels);

		// Confirmation e-mail
		$objEmail = new \Email();
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));
		$objEmail->text = \StringUtil::parseSimpleTokens($this->nl_unsubscribe, $arrData);
		$objEmail->sendTo($strEmail);

		// Redirect to the jumpTo page
		if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel)
		{
			/** @var PageModel $objTarget */
			$this->redirect($objTarget->getFrontendUrl());
		}

		\System::getContainer()->get('session')->getFlashBag()->set('nl_removed', $GLOBALS['TL_LANG']['MSC']['nl_removed']);

		$this->reload();
	}
}
