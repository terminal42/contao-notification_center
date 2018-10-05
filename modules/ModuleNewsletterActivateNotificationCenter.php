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
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['newsletterActivateNotificationCenter'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->nl_channels = deserialize($this->nl_channels);

        // Return if there are no channels
        if (empty($this->nl_channels) || !\is_array($this->nl_channels))
        {
            return '';
        }

        if (!\Input::get('token')) {
            return '';
        }

        $this->activateRecipient();

        return parent::generate();
    }

    protected function compile()
    {
    }

    /**
     * Activate a recipient
     */
    protected function activateRecipient()
    {
        // Check the token
        $objRecipient = \NewsletterRecipientsModel::findByToken(\Input::get('token'));

        if ($objRecipient === null)
        {
            $this->Template->mclass = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['invalidToken'];

            return;
        }

        $time = time();
        $arrAdd = array();
        $arrCids = array();

        // Update the subscriptions
        while ($objRecipient->next())
        {
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

        // HOOK: post activation callback
        if (isset($GLOBALS['TL_HOOKS']['activateRecipient']) && \is_array($GLOBALS['TL_HOOKS']['activateRecipient']))
        {
            foreach ($GLOBALS['TL_HOOKS']['activateRecipient'] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($objRecipient->email, $arrAdd, $arrCids);
            }
        }

        $this->sendNotification($objRecipient->id, $arrCids);
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
        $arrData['channel'] = $arrData['channels'] = $arrChannels;
        $arrData['channel_ids'] = $arrCids;
        $arrData['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrData['admin_name'] = $GLOBALS['TL_ADMIN_NAME'];
        $arrData['subject'] = sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'], \Idna::decode(\Environment::get('host')));

        $objNotification->send($arrData);
    }
}
