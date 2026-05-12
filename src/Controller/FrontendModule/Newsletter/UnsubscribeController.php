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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsFrontendModule('newsletterUnsubscribeNotificationCenter', 'newsletter', 'nl_default')]
class UnsubscribeController extends ModuleUnsubscribe
{
    private Request|null $request = null;

    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    public function __invoke(Request $request, ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        $this->request = $request;

        $content = $this->generate();

        $this->request = null;

        return new Response($content);
    }

    /**
     * @param array<int> $arrRemove
     */
    #[\Override]
    protected function removeRecipient($strEmail, $arrRemove): void
    {
        // Remove the subscriptions
        if (($objRemove = NewsletterRecipientsModel::findByEmailAndPids($strEmail, $arrRemove)) !== null) {
            while ($objRemove->next()) {
                $strHash = md5((string) $objRemove->email); // @phpstan-ignore-line

                // Add a deny list entry (see #4999)
                if (null === NewsletterDenyListModel::findByHashAndPid($strHash, $objRemove->pid)) { // @phpstan-ignore-line
                    $objDenyList = new NewsletterDenyListModel();
                    $objDenyList->pid = $objRemove->pid; // @phpstan-ignore-line
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
        $tokens['domain'] = $this->request?->getHost() ?? '';
        $tokens['channels'] = implode(', ', $arrChannels);
        $tokens['channel_ids'] = implode(', ', $arrRemove);

        // Make sending the notification optional so that you can use this module to NOT
        // send any notification which is not possible in the Contao core
        if ($this->nc_notification) {
            $this->notificationCenter->sendNotification((int) $this->nc_notification, $tokens);
        }

        // Redirect to the jumpTo page
        if (($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            /** @var PageModel $objTarget */
            $this->redirect($objTarget->getAbsoluteUrl());
        }

        System::getContainer()->get('request_stack')->getSession()->getFlashBag()->set('nl_removed', $GLOBALS['TL_LANG']['MSC']['nl_removed']);

        $this->reload();
    }
}
