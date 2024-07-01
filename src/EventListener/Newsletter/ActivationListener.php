<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Newsletter;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Module;
use Contao\ModuleSubscribe;
use Contao\NewsletterChannelModel;
use Contao\PageModel;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsHook('activateRecipient')]
class ActivationListener
{
    public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly ContaoFramework $contaoFramework,
    ) {
    }

    /**
     * @param array<int> $recipientIds
     * @param array<int> $channelIds
     */
    public function __invoke(string $email, array $recipientIds, array $channelIds, Module $module): void
    {
        if (!$module instanceof ModuleSubscribe) {
            return;
        }

        if ($module->nc_activation_notification) {
            $this->sendNotification($email, $channelIds, $module);
        }

        if ($module->nc_newsletter_activation_jumpTo) {
            $targetPage = $this->contaoFramework->getAdapter(PageModel::class)
                ->findById($module->nc_newsletter_activation_jumpTo)
            ;

            if ($targetPage instanceof PageModel) {
                throw new RedirectResponseException($targetPage->getAbsoluteUrl());
            }
        }
    }

    /**
     * @param array<int> $channelIds
     */
    private function sendNotification(string $email, array $channelIds, Module $module): void
    {
        $this->contaoFramework->initialize();

        $channelModels = $this->contaoFramework->getAdapter(NewsletterChannelModel::class)
            ->findByIds($channelIds)
        ;
        $channelTitles = $channelModels ? $channelModels->fetchEach('title') : [];

        // Prepare the simple tokens
        $tokens = [];
        $tokens['recipient_email'] = $email;
        $tokens['channels'] = implode(', ', $channelTitles);
        $tokens['channel_ids'] = implode(', ', $channelIds);

        $this->notificationCenter->sendNotification((int) $module->nc_activation_notification, $tokens);
    }
}
