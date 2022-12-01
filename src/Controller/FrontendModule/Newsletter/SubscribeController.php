<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Controller\FrontendModule\Newsletter;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\Environment;
use Contao\Idna;
use Contao\ModuleModel;
use Contao\ModuleSubscribe;
use Contao\NewsletterChannelModel;
use Contao\NewsletterDenyListModel;
use Contao\NewsletterRecipientsModel;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\Newsletter\NewsletterSubscribeNotificationType;

#[AsFrontendModule('newsletterSubscribeNotificationCenter', 'newsletter', 'nl_default')]
class SubscribeController extends ModuleSubscribe
{
    public function __construct(private NotificationCenter $notificationCenter)
    {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function addRecipient($strEmail, $arrNew): void
    {
        // Remove old subscriptions that have not been activated yet
        if (($objOld = NewsletterRecipientsModel::findOldSubscriptionsByEmailAndPids($strEmail, $arrNew)) !== null) {
            while ($objOld->next()) {
                $objOld->delete();
            }
        }

        $time = time();
        $arrRelated = [];

        // Add the new subscriptions
        foreach ($arrNew as $id) {
            $objRecipient = new NewsletterRecipientsModel();
            $objRecipient->pid = $id;
            $objRecipient->tstamp = $time;
            $objRecipient->email = $strEmail;
            $objRecipient->active = false;
            $objRecipient->addedOn = $time;
            $objRecipient->save();

            // Remove the deny list entry (see #4999)
            if (($objDenyList = NewsletterDenyListModel::findByHashAndPid(md5($strEmail), $id)) !== null) {
                $objDenyList->delete();
            }

            $arrRelated['tl_newsletter_recipients'][] = $objRecipient->id;
        }

        $optIn = System::getContainer()->get('contao.opt_in');
        $optInToken = $optIn->create('nl', $strEmail, $arrRelated);

        // Get the channels
        $objChannel = NewsletterChannelModel::findByIds($arrNew);
        $arrChannels = $objChannel ? $objChannel->fetchEach('title') : [];

        // Prepare the simple tokens
        $tokens = [];
        $tokens['recipient_email'] = $strEmail;
        $tokens['token'] = $optInToken->getIdentifier();
        $tokens['link'] = Idna::decode(Environment::get('url')).Environment::get('requestUri').(false !== strpos(Environment::get('requestUri'), '?') ? '&' : '?').'token='.$optInToken->getIdentifier();
        $tokens['channels'] = implode(', ', $arrChannels);
        $tokens['channel_ids'] = implode(', ', $arrNew);

        $tokens = $this->notificationCenter->createTokenCollectionFromArray($tokens, NewsletterSubscribeNotificationType::NAME);
        $this->notificationCenter->sendNotification((int) $this->nc_notification, $tokens, $GLOBALS['objPage']->language);

        // Redirect to the jumpTo page
        if (($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            /** @var PageModel $objTarget */
            $this->redirect($objTarget->getFrontendUrl());
        }

        System::getContainer()->get('request_stack')->getSession()->getFlashBag()->set('nl_confirm', $GLOBALS['TL_LANG']['MSC']['nl_confirm']);

        $this->reload();
    }
}
