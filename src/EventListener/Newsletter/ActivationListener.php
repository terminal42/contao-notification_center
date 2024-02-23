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

    public function __invoke(string $email, array $recipientIds, array $channelIds, Module $module): void
    {
        if (!$module instanceof ModuleSubscribe) {
            return;
        }

        if (!$module->nc_activation_notification) {
            return;
        }

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

        if ($module->nc_newsletter_activation_jumpTo) {
            $targetPage = $this->contaoFramework->getAdapter(PageModel::class)
                ->findByPk($module->nc_newsletter_activation_jumpTo)
            ;

            if ($targetPage instanceof PageModel) {
                throw new RedirectResponseException($targetPage->getFrontendUrl());
            }
        }
    }
}
