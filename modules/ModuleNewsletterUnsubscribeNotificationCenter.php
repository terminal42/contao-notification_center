<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use NotificationCenter\Model\Notification;

/**
 * Front end module "newsletter unsubscribe".
 *
 * @property bool   $nl_hideChannels
 * @property string $nl_unsubscribe
 * @property array  $nl_channels
 * @property string $nl_template
 * @property int    $nc_notification
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
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

        $this->processForm($strFormId, $objCaptchaWidget, 'removeRecipient');
        $this->compileConfirmationMessage();

        // Default template variables
        $this->Template->captach  = $objCaptchaWidget ? $objCaptchaWidget->parse() : '';
        $this->Template->channels = $this->compileChannels();
        $this->Template->showChannels = !$this->nl_hideChannels;
        $this->Template->email = \Input::get('email');
        $this->Template->submit = specialchars($GLOBALS['TL_LANG']['MSC']['unsubscribe']);
        $this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
        $this->Template->emailLabel = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->formId = $strFormId;
        $this->Template->id = $this->id;
    }

    /**
     * Validate the subscription form
     *
     * @param Widget $objWidget
     *
     * @return array|bool
     */
    protected function validateForm(Widget $objWidget=null)
    {
        // Validate the e-mail address
        $varInput = \Idna::encodeEmail(\Input::post('email', true));

        if (!\Validator::isEmail($varInput))
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['email'];

            return false;
        }

        $this->Template->email = $varInput;

        // Validate the channel selection
        $arrChannels = \Input::post('channels');

        if (!\is_array($arrChannels))
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noChannels'];

            return false;
        }

        $arrChannels = array_intersect($arrChannels, $this->nl_channels); // see #3240

        if (empty($arrChannels) || !\is_array($arrChannels))
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noChannels'];

            return false;
        }

        $this->Template->selectedChannels = $arrChannels;

        // Check if there are any new subscriptions
        $arrSubscriptions = array();

        if (($objSubscription = \NewsletterRecipientsModel::findBy(array("email=? AND active=1"), $varInput)) !== null)
        {
            $arrSubscriptions = $objSubscription->fetchEach('pid');
        }

        $arrRemove = array_intersect($arrChannels, $arrSubscriptions);

        if (empty($arrRemove) || !\is_array($arrRemove))
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['unsubscribed'];

            return false;
        }

        // Validate the captcha
        if ($objWidget !== null)
        {
            $objWidget->validate();

            if ($objWidget->hasErrors())
            {
                return false;
            }
        }

        return array($varInput, $arrRemove);
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

        // HOOK: post unsubscribe callback
        if (isset($GLOBALS['TL_HOOKS']['removeRecipient']) && \is_array($GLOBALS['TL_HOOKS']['removeRecipient']))
        {
            foreach ($GLOBALS['TL_HOOKS']['removeRecipient'] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($strEmail, $arrRemove);
            }
        }

        $this->sendNotification($strEmail, $arrRemove);
        $this->redirectToJumpToPage();

        if (version_compare(VERSION, '4.1', '>='))
        {
            \System::getContainer()->get('session')->getFlashBag()->set('nl_removed', $GLOBALS['TL_LANG']['MSC']['nl_removed']);
        }
        else
        {
            $_SESSION['UNSUBSCRIBE_CONFIRM'] = $GLOBALS['TL_LANG']['MSC']['nl_removed'];
        }

        $this->reload();
    }

    protected function compileConfirmationMessage()
    {
        if (version_compare(VERSION, '4.1', '>='))
        {
            $session = \System::getContainer()->get('session');
            $flashBag = $session->getFlashBag();

            // Confirmation message
            if ($session->isStarted() && $flashBag->has('nl_removed'))
            {
                $arrMessages = $flashBag->get('nl_removed');

                $this->Template->mclass = 'confirm';
                $this->Template->message = $arrMessages[0];
            }

            return;
        }

        // Error message
        if (strlen($_SESSION['UNSUBSCRIBE_ERROR']))
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $_SESSION['UNSUBSCRIBE_ERROR'];
            $_SESSION['UNSUBSCRIBE_ERROR'] = '';
        }

        // Confirmation message
        if (strlen($_SESSION['UNSUBSCRIBE_CONFIRM']))
        {
            $this->Template->mclass = 'confirm';
            $this->Template->message = $_SESSION['UNSUBSCRIBE_CONFIRM'];
            $_SESSION['UNSUBSCRIBE_CONFIRM'] = '';
        }
    }

    protected function sendNotification($strEmail, array $arrRemove)
    {
        $objNotification = Notification::findByPk($this->nc_notification);
        if (!$objNotification) {
            return;
        }

        // Get the channels
        $objChannels = \NewsletterChannelModel::findByIds($arrRemove);
        $arrChannels = $objChannels ? $objChannels->fetchEach('title') : [];

        // Prepare the simple token data
        $arrData = array();
        $arrData['recipient_email'] = $strEmail;
        $arrData['domain'] = \Idna::decode(\Environment::get('host'));
        $arrData['channel'] = $arrData['channels'] = $arrChannels;
        $arrData['channel_ids'] = $arrRemove;
        $arrData['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrData['admin_name'] = $GLOBALS['TL_ADMIN_NAME'];
        $arrData['subject'] = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));
        $arrData['text'] = $this->nl_unsubscribe;

        $objNotification->send($arrData);
    }
}
