<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Controller\FrontendModule\Newsletter;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\ModuleUnsubscribe;
use Contao\NewsletterChannelModel;
use Contao\NewsletterDenyListModel;
use Contao\NewsletterRecipientsModel;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\Newsletter\NewsletterUnsubscribeNotificationType;

#[AsFrontendModule('newsletterUnsubscribeNotificationCenter', 'newsletter', 'nl_default')]
class UnsubscribeController extends ModuleUnsubscribe
{
    public function __construct(private NotificationCenter $notificationCenter)
    {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function removeRecipient($strEmail, $arrRemove): void
    {
        // Remove the subscriptions
        if (($objRemove = NewsletterRecipientsModel::findByEmailAndPids($strEmail, $arrRemove)) !== null) {
            while ($objRemove->next()) {
                $strHash = md5($objRemove->email);

                // Add a deny list entry (see #4999)
                if (null === NewsletterDenyListModel::findByHashAndPid($strHash, $objRemove->pid)) {
                    $objDenyList = new NewsletterDenyListModel();
                    $objDenyList->pid = $objRemove->pid;
                    $objDenyList->hash = $strHash;
                    $objDenyList->save();
                }

                $objRemove->delete();
            }
        }

        // Get the channels
        $objChannels = NewsletterChannelModel::findByIds($arrRemove);
        $arrChannels = $objChannels->fetchEach('title');

        // HOOK: post unsubscribe callback
        if (isset($GLOBALS['TL_HOOKS']['removeRecipient']) && \is_array($GLOBALS['TL_HOOKS']['removeRecipient'])) {
            foreach ($GLOBALS['TL_HOOKS']['removeRecipient'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($strEmail, $arrRemove);
            }
        }

        // Prepare the simple tokens
        $tokens = [];
        $tokens['recipient_email'] = $strEmail;
        $tokens['channels'] = implode(', ', $arrChannels);
        $tokens['channel_ids'] = implode(', ', $arrRemove);

        $tokens = $this->notificationCenter->createTokenCollectionFromArray($tokens, NewsletterUnsubscribeNotificationType::NAME);
        $this->notificationCenter->sendNotification((int) $this->nc_notification, $tokens, $GLOBALS['objPage']->language);

        // Redirect to the jumpTo page
        if (($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            /** @var PageModel $objTarget */
            $this->redirect($objTarget->getFrontendUrl());
        }

        System::getContainer()->get('request_stack')->getSession()->getFlashBag()->set('nl_removed', $GLOBALS['TL_LANG']['MSC']['nl_removed']);

        $this->reload();
    }
}
