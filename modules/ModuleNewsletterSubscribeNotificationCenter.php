<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Patchwork\Utf8;

/**
 * Front end module "newsletter subscribe".
 *
 * @property string $nl_subscribe
 * @property array  $nl_channels
 * @property string $nl_template
 * @property string $nl_text
 * @property bool   $nl_hideChannels
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleNewsletterSubscribeNotificationCenter extends ModuleSubscribe
{
	/**
	 * Generate the module
	 */
	protected function compile()
	{
        $this->setCustomTemplate();

        $this->Template->email = '';
		$this->Template->captcha = '';

        $objCaptchaWidget = $this->createCaptchaWidgetIfEnabled();

        $strFormId = 'tl_subscribe_' . $this->id;

		// Validate the form
		if (\Input::post('FORM_SUBMIT') == $strFormId)
		{
			$varSubmitted = $this->validateForm($objCaptchaWidget);

			if ($varSubmitted !== false)
			{
				\call_user_func_array(array($this, 'addRecipient'), $varSubmitted);
			}
		}

		// Add the captcha widget to the template
		if ($objCaptchaWidget !== null)
		{
			$this->Template->captcha = $objCaptchaWidget->parse();
		}

		$session = \System::getContainer()->get('session');
		$flashBag = $session->getFlashBag();

		// Confirmation message
		if ($session->isStarted() && $flashBag->has('nl_confirm'))
		{
			$arrMessages = $flashBag->get('nl_confirm');

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
		$this->Template->submit = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['subscribe']);
		$this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
		$this->Template->emailLabel = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
		$this->Template->action = \Environment::get('indexFreeRequest');
		$this->Template->formId = $strFormId;
		$this->Template->id = $this->id;
		$this->Template->text = $this->nl_text;
	}

	/**
	 * Add a new recipient
	 *
	 * @param string $strEmail
	 * @param array  $arrNew
	 */
	protected function addRecipient($strEmail, $arrNew)
	{
		// Remove old subscriptions that have not been activated yet
		if (($objOld = \NewsletterRecipientsModel::findOldSubscriptionsByEmailAndPids($strEmail, $arrNew)) !== null)
		{
			while ($objOld->next())
			{
				$objOld->delete();
			}
		}

		$time = time();
		$strToken = md5(uniqid(mt_rand(), true));

		// Add the new subscriptions
		foreach ($arrNew as $id)
		{
			$objRecipient = new \NewsletterRecipientsModel();
			$objRecipient->pid = $id;
			$objRecipient->tstamp = $time;
			$objRecipient->email = $strEmail;
			$objRecipient->active = '';
			$objRecipient->addedOn = $time;
			$objRecipient->ip = \Environment::get('ip');
			$objRecipient->token = $strToken;
			$objRecipient->confirmed = '';
			$objRecipient->save();

			// Remove the blacklist entry (see #4999)
			if (($objBlacklist = \NewsletterBlacklistModel::findByHashAndPid(md5($strEmail), $id)) !== null)
			{
				$objBlacklist->delete();
			}
		}

		// Get the channels
		$objChannel = \NewsletterChannelModel::findByIds($arrNew);

		// Prepare the simple token data
		$arrData = array();
		$arrData['token'] = $strToken;
		$arrData['domain'] = \Idna::decode(\Environment::get('host'));
		$arrData['link'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $strToken;
		$arrData['channel'] = $arrData['channels'] = implode("\n", $objChannel->fetchEach('title'));

		// Activation e-mail
		$objEmail = new \Email();
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));
		$objEmail->text = \StringUtil::parseSimpleTokens($this->nl_subscribe, $arrData);
		$objEmail->sendTo($strEmail);

		// Redirect to the jumpTo page
		if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel)
		{
			/** @var PageModel $objTarget */
			$this->redirect($objTarget->getFrontendUrl());
		}

		\System::getContainer()->get('session')->getFlashBag()->set('nl_confirm', $GLOBALS['TL_LANG']['MSC']['nl_confirm']);

		$this->reload();
	}

    protected function setCustomTemplate()
    {
        if ($this->nl_template) {
            $this->Template = new \FrontendTemplate($this->nl_template);
            $this->Template->setData($this->arrData);
        }
    }

    /**
     * @return Widget|null
     */
    protected function createCaptchaWidgetIfEnabled()
    {
        $objWidget = null;

        // Set up the captcha widget
        if (!$this->disableCaptcha) {
            $arrField = [
                'name'      => 'subscribe_' . $this->id,
                'label'     => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
                'inputType' => 'captcha',
                'eval'      => ['mandatory' => true]
            ];

            /** @var Widget $objWidget */
            $objWidget = new \FormCaptcha(\FormCaptcha::getAttributesFromDca($arrField, $arrField['name']));
        }

        return $objWidget;
    }
}
