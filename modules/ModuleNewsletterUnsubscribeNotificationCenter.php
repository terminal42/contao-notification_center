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

class ModuleNewsletterUnsubscribeNotificationCenter extends ModuleUnsubscribe
{
    use NewsletterModuleTrait;

    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->setCustomTemplate();

        $objCaptchaWidget = $this->createCaptchaWidgetIfEnabled();
        $strFormId = 'tl_unsubscribe_' . $this->id;

        $this->processForm($strFormId, $objCaptchaWidget, 'removeNewsletterRecipient');
        $this->compileConfirmationMessage();

        // Default template variables
        $this->Template->captcha = $objCaptchaWidget ? $objCaptchaWidget->parse() : '';
        $this->Template->channels = $this->compileChannels();
        $this->Template->showChannels = !$this->nl_hideChannels;
        $this->Template->email = Input::get('email');
        $this->Template->submit = specialchars($GLOBALS['TL_LANG']['MSC']['unsubscribe']);
        $this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
        $this->Template->emailLabel = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        $this->Template->action = Environment::get('indexFreeRequest');
        $this->Template->formId = $strFormId;
        $this->Template->id = $this->id;
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
        $varInput = Idna::encodeEmail(Input::post('email', true));

        if (!Validator::isEmail($varInput)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['email'];

            return false;
        }

        $this->Template->email = $varInput;

        // Validate the channel selection
        $arrChannels = Input::post('channels');

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

        if (($objSubscription = NewsletterRecipientsModel::findBy(
                array("email=? AND active=1"),
                $varInput
            )) !== null) {
            $arrSubscriptions = $objSubscription->fetchEach('pid');
        }

        $arrRemove = array_intersect($arrChannels, $arrSubscriptions);

        if (empty($arrRemove) || !\is_array($arrRemove)) {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['unsubscribed'];

            return false;
        }

        // Validate the captcha
        if ($objWidget !== null) {
            $objWidget->validate();

            if ($objWidget->hasErrors()) {
                return false;
            }
        }

        return array(
            $varInput,
            $arrRemove,
        );
    }

    /**
     * Remove the recipient
     *
     * @param string $strEmail
     * @param array  $arrRemove
     */
    protected function removeNewsletterRecipient($strEmail, $arrRemove)
    {
        // Remove the subscriptions
        if (($objRemove = NewsletterRecipientsModel::findByEmailAndPids($strEmail, $arrRemove)) !== null) {
            while ($objRemove->next()) {
                $strHash = md5($objRemove->email);

                // Add a blacklist entry (see #4999)
                $objBlacklist = NewsletterBlacklistModel::findByHashAndPid($strHash, $objRemove->pid);
                if ($objBlacklist === null) {
                    $objBlacklist = new NewsletterBlacklistModel();
                    $objBlacklist->pid = $objRemove->pid;
                    $objBlacklist->hash = $strHash;
                    $objBlacklist->save();
                }

                $objRemove->delete();
            }
        }

        // HOOK: post unsubscribe callback
        if (isset($GLOBALS['TL_HOOKS']['removeRecipient']) && \is_array($GLOBALS['TL_HOOKS']['removeRecipient'])) {
            foreach ($GLOBALS['TL_HOOKS']['removeRecipient'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($strEmail, $arrRemove);
            }
        }

        $this->sendNotification($strEmail, $arrRemove);
        $this->redirectToJumpToPage();

        System::getContainer()
               ->get('session')
               ->getFlashBag()
               ->set('nl_removed', $GLOBALS['TL_LANG']['MSC']['nl_removed'])
        ;

        $this->reload();
    }

    protected function compileConfirmationMessage()
    {
        $session = System::getContainer()->get('session');
        $flashBag = $session->getFlashBag();

        // Confirmation message
        if ($session->isStarted() && $flashBag->has('nl_removed')) {
            $arrMessages = $flashBag->get('nl_removed');

            $this->Template->mclass = 'confirm';
            $this->Template->message = $arrMessages[0];
        }
    }

    protected function sendNotification($strEmail, array $arrRemove)
    {
        $objNotification = Notification::findByPk($this->nc_notification);
        if (!$objNotification) {
            return;
        }

        // Get the channels
        $objChannels = NewsletterChannelModel::findByIds($arrRemove);
        $arrChannels = $objChannels ? $objChannels->fetchEach('title') : [];

        // Prepare the simple token data
        $arrData = array();
        $arrData['recipient_email'] = $strEmail;
        $arrData['domain'] = Idna::decode(Environment::get('host'));
        $arrData['channels'] = implode(', ', $arrChannels);
        $arrData['channel_ids'] = implode(', ', $arrRemove);
        $arrData['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrData['admin_name'] = $GLOBALS['TL_ADMIN_NAME'];
        $arrData['subject'] = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], Idna::decode(Environment::get('host')));
        $arrData['text'] = $this->nl_unsubscribe;

        $objNotification->send($arrData);
    }
}
