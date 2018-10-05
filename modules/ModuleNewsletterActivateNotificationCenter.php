<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2018, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace Contao;

final class ModuleNewsletterActivateNotificationCenter extends Module
{
    protected $strTemplate = 'nl_default';

    protected function compile()
    {
        // Activate e-mail address
        if (\Input::get('token'))
        {
            $this->activateRecipient();

            return;
        }
    }

    /**
     * Activate a recipient
     */
    protected function activateRecipient()
    {
        $this->Template = new \FrontendTemplate('mod_newsletter');

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

        // Confirm activation
        $this->Template->mclass = 'confirm';
        $this->Template->message = $GLOBALS['TL_LANG']['MSC']['nl_activate'];
    }
}
