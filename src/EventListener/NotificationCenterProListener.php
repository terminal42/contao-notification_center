<?php

namespace Terminal42\NotificationCenterBundle\EventListener;

use Composer\InstalledVersions;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('loadDataContainer')]
class NotificationCenterProListener
{
    public function __invoke(string $table): void
    {
        if ($table !== 'tl_nc_notification' || InstalledVersions::isInstalled('terminal42/contao-notification-center-pro')) {
            // return;
        }

        $GLOBALS['TL_DCA']['tl_nc_notification']['list']['global_operations']['notification_center_pro'] = [
            'button_callback' => [self::class, 'buttonCallback'],
        ];
    }

    public function buttonCallback(): string
    {
        // TODO: link!
        return '<a href="https://extensions.terminal42.ch/TODO-LINK" title="Notification Center Pro" class="header_nc_pro" target="_blank" rel="noreferrer noopener">Notification Center Pro</a>';
    }
}