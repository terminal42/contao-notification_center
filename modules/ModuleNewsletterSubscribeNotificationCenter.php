<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2018, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace Contao;

use NotificationCenter\Model\Notification;

class ModuleNewsletterSubscribeNotificationCenter extends ModuleSubscribe
{
    use NewsletterModuleTrait;

    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->setCustomTemplate();

        $objCaptchaWidget = $this->createCaptchaWidgetIfEnabled();

        $strFormId = 'tl_subscribe_' . $this->id;

        $this->processForm($strFormId, $objCaptchaWidget, 'addNewsletterRecipient');
        $this->compileConfirmationMessage();

        $this->Template->email = '';
        $this->Template->captcha = $objCaptchaWidget ? $objCaptchaWidget->parse() : '';
        $this->Template->channels = $this->compileChannels();
        $this->Template->showChannels = !$this->nl_hideChannels;
        $this->Template->submit = specialchars($GLOBALS['TL_LANG']['MSC']['subscribe']);
        $this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
        $this->Template->emailLabel = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->formId = $strFormId;
        $this->Template->id = $this->id;
        $this->Template->text = $this->nl_text;
        $this->Template->requestToken = REQUEST_TOKEN;
    }

    /**
     * Validate the subscription form
     *
     * @param Widget $objWidget
     *
     * @return array|bool
     */
    protected function validateForm(Widget $objWidget = null)
    {
        // Validate the e-mail address
        $varInput = \Idna::encodeEmail(\Input::post('email', true));

        if (!\Validator::isEmail($varInput)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['email'];

            return false;
        }

        $this->Template->email = $varInput;

        // Validate the channel selection
        $arrChannels = \Input::post('channels');

        if (!\is_array($arrChannels)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noChannels'];

            return false;
        }

        $arrChannels = array_intersect($arrChannels, $this->nl_channels); // see #3240

        if (empty($arrChannels) || !\is_array($arrChannels)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noChannels'];

            return false;
        }

        $this->Template->selectedChannels = $arrChannels;

        // Check if there are any new subscriptions
        $arrSubscriptions = array();

        if (($objSubscription = \NewsletterRecipientsModel::findBy(array("email=? AND active=1"), $varInput)) !== null) {
            $arrSubscriptions = $objSubscription->fetchEach('pid');
        }

        $arrNew = array_diff($arrChannels, $arrSubscriptions);

        if (empty($arrNew) || !\is_array($arrNew)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['subscribed'];

            return false;
        }

        // Validate the captcha
        if ($objWidget !== null) {
            $objWidget->validate();

            if ($objWidget->hasErrors()) {
                return false;
            }
        }

        return array($varInput, $arrNew);
    }

    /**
     * Add a new recipient
     *
     * @param string $strEmail
     * @param array  $arrNew
     */
    protected function addNewsletterRecipient($strEmail, $arrNew)
    {
        // Remove old subscriptions that have not been activated yet
        if (($objOld = \NewsletterRecipientsModel::findOldSubscriptionsByEmailAndPids($strEmail, $arrNew)) !== null) {
            while ($objOld->next()) {
                $objOld->delete();
            }
        }

        $time = time();
        $strToken = md5(uniqid(mt_rand(), true));
        $arrRelated = [];

        // Add the new subscriptions
        foreach ($arrNew as $id) {
            $objRecipient = new \NewsletterRecipientsModel();
            $objRecipient->pid = $id;
            $objRecipient->tstamp = $time;
            $objRecipient->email = $strEmail;
            $objRecipient->active = '';
            $objRecipient->addedOn = $time;
            if (version_compare(VERSION, '4.7', '<')) {
                $objRecipient->ip = \Environment::get('ip');
                $objRecipient->token = $strToken;
                $objRecipient->confirmed = '';
            }
            $objRecipient->save();

            $arrRelated['tl_newsletter_recipients'][] = $objRecipient->id;

            // Remove the blacklist entry (see #4999)
            if (version_compare(VERSION, '4.1', '>=')
                && ($objBlacklist = \NewsletterBlacklistModel::findByHashAndPid(md5($strEmail), $id)) !== null
            ) {
                $objBlacklist->delete();
            }
        }

        if (version_compare(VERSION, '4.7', '>=')) {
            /** @var \Contao\CoreBundle\OptIn\OptIn $optIn */
            $optIn = \System::getContainer()->get('contao.opt-in');
            $strToken = $optIn->create('nl', $strEmail, $arrRelated)->getIdentifier();
        }

        $this->sendNotification($strToken, $strEmail, $arrNew);
        $this->redirectToJumpToPage();

        if (version_compare(VERSION, '4.1', '>=')) {
            \System::getContainer()
                   ->get('session')
                   ->getFlashBag()
                   ->set('nl_confirm', $GLOBALS['TL_LANG']['MSC']['nl_confirm'])
            ;
        } else {
            $_SESSION['SUBSCRIBE_CONFIRM'] = $GLOBALS['TL_LANG']['MSC']['nl_confirm'];
        }

        $this->reload();
    }

    protected function compileConfirmationMessage()
    {
        if (version_compare(VERSION, '4.1', '>=')) {
            $session = \System::getContainer()->get('session');
            $flashBag = $session->getFlashBag();

            if ($session->isStarted() && $flashBag->has('nl_confirm')) {
                $arrMessages = $flashBag->get('nl_confirm');

                $this->Template->mclass = 'confirm';
                $this->Template->message = $arrMessages[0];
            }

            return;
        }

        // Error message
        if (strlen($_SESSION['SUBSCRIBE_ERROR'])) {
            $this->Template->mclass = 'error';
            $this->Template->message = $_SESSION['SUBSCRIBE_ERROR'];
            $this->Template->hasError = true;
            $_SESSION['SUBSCRIBE_ERROR'] = '';
        }

        // Confirmation message
        if (strlen($_SESSION['SUBSCRIBE_CONFIRM'])) {
            $this->Template->mclass = 'confirm';
            $this->Template->message = $_SESSION['SUBSCRIBE_CONFIRM'];
            $this->Template->hasError = false;
            $_SESSION['SUBSCRIBE_CONFIRM'] = '';
        }
    }

    protected function sendNotification($strToken, $strEmail, array $arrNew)
    {
        $objNotification = Notification::findByPk($this->nc_notification);
        if (!$objNotification) {
            return;
        }

        $objChannel = \NewsletterChannelModel::findByIds($arrNew);
        $arrChannels = $objChannel ? $objChannel->fetchEach('title') : [];

        // Prepare the simple token data
        $arrData = array();
        $arrData['recipient_email'] = $strEmail;
        $arrData['token'] = $strToken;
        $arrData['domain'] = \Idna::decode(\Environment::get('host'));
        $arrData['link'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $strToken;
        $arrData['channels'] = implode(', ', $arrChannels);
        $arrData['channel_ids'] = implode(', ', $arrNew);
        $arrData['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrData['admin_name'] = $GLOBALS['TL_ADMIN_NAME'];
        $arrData['subject'] = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));

        $objNotification->send($arrData);
    }
}
