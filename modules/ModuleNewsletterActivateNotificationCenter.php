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

class ModuleNewsletterActivateNotificationCenter extends Module
{
    use NewsletterModuleTrait;

    protected $strTemplate = 'mod_newsletter';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD']['newsletterActivateNotificationCenter'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->nl_channels = deserialize($this->nl_channels);

        // Return if there are no channels
        if (empty($this->nl_channels) || !\is_array($this->nl_channels)) {
            return '';
        }

        if (!\Input::get('token')) {
            return '';
        }

        return parent::generate();
    }

    protected function compile()
    {
        $this->activateRecipient(\Input::get('token'));
    }

    /**
     * Activate a recipient
     *
     * @param string $token
     */
    protected function activateRecipient($token)
    {
        // Check the token
        if (version_compare(VERSION, '4.7', '>=')) {
            /** @var \Contao\CoreBundle\OptIn\OptIn $optIn */
            $optIn = \System::getContainer()->get('contao.opt-in');

            if (!($optInToken = $optIn->find($token)) || $optInToken->isConfirmed() || \count($arrRelated = $optInToken->getRelatedRecords()) < 1)
            {
                $this->Template->type = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['MSC']['accountError'];

                return;
            }

            $strEmail = $optInToken->getEmail();
        } else {
            $objRecipient = \NewsletterRecipientsModel::findByToken($token);

            if ($objRecipient === null) {
                $this->Template->mclass = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['ERR']['invalidToken'];

                return;
            }

            $strEmail = $objRecipient->email;
        }

        $time = time();
        $arrAdd = array();
        $arrCids = array();

        if (version_compare(VERSION, '4.7', '>=')) {
            foreach ($arrRelated as $strTable=>$intId)
            {
                if ($strTable == 'tl_newsletter_recipients' && ($objRecipient = \NewsletterRecipientsModel::findByPk($intId)))
                {
                    $arrAdd[] = $objRecipient->id;
                    $arrCids[] = $objRecipient->pid;

                    $objRecipient->tstamp = $time;
                    $objRecipient->active = '1';
                    $objRecipient->save();
                }
            }

            $optInToken->confirm();

        } else {
            // Update the subscriptions
            while ($objRecipient->next()) {
                /** @var NewsletterChannelModel $objChannel */
                $objChannel = $objRecipient->getRelated('pid');

                $arrAdd[] = $objRecipient->id;
                $arrCids[] = $objChannel->id;

                $objRecipient->active = 1;
                $objRecipient->token = '';
                $objRecipient->pid = $objChannel->id;
                $objRecipient->confirmed = $time;
                $objRecipient->save();
            }
        }

        // HOOK: post activation callback
        if (isset($GLOBALS['TL_HOOKS']['activateRecipient']) && \is_array($GLOBALS['TL_HOOKS']['activateRecipient'])) {
            foreach ($GLOBALS['TL_HOOKS']['activateRecipient'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($strEmail, $arrAdd, $arrCids);
            }
        }

        $this->sendNotification($strEmail, $arrCids);
        $this->redirectToJumpToPage();

        // Confirm activation
        $this->Template->mclass = 'confirm';
        $this->Template->message = $GLOBALS['TL_LANG']['MSC']['nl_activate'];
    }

    protected function sendNotification($strEmail, array $arrCids)
    {
        $objNotification = Notification::findByPk($this->nc_notification);
        if (!$objNotification) {
            return;
        }

        $objChannel = \NewsletterChannelModel::findByIds($arrCids);
        $arrChannels = $objChannel ? $objChannel->fetchEach('title') : [];

        // Prepare the simple token data
        $arrData = array();
        $arrData['recipient_email'] = $strEmail;
        $arrData['domain'] = \Idna::decode(\Environment::get('host'));
        $arrData['channels'] = implode(', ', $arrChannels);
        $arrData['channel_ids'] = implode(', ', $arrCids);
        $arrData['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrData['admin_name'] = $GLOBALS['TL_ADMIN_NAME'];
        $arrData['subject'] = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));

        $objNotification->send($arrData);
    }
}
