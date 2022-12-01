<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\GetNotificationTypeForModuleConfigEvent;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class ModuleListener
{
    public function __construct(private ConfigLoader $configLoader, private EventDispatcherInterface $eventDispatcher, private NotificationCenter $notificationCenter)
    {
    }

    #[AsCallback(table: 'tl_module', target: 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        if (null === ($moduleConfig = $this->configLoader->loadModule((int) $dc->id))) {
            return;
        }

        switch ($moduleConfig->getType()) {
            case 'lostPasswordNotificationCenter':
                $this->handleLostPasswordModule();
                break;
            case 'registrationNotificationCenter':
                $this->handleRegistrationModule();
                break;
            case 'personalData':
                $this->handlePersonalDataModule();
                break;
            case 'newsletterSubscribeNotificationCenter':
                $this->handleNewsletterSubscribeModule();
                break;
            case 'newsletterUnsubscribeNotificationCenter':
                $this->handleNewsletterUnubscribeModule();
                break;
        }
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.nc_notification.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nc_activation_notification.options')]
    public function onNotificationOptionsCallback(DataContainer $dc): array
    {
        if (null === ($moduleConfig = $this->configLoader->loadModule((int) $dc->id))) {
            return [];
        }

        $event = new GetNotificationTypeForModuleConfigEvent($moduleConfig, $dc->field);

        $this->eventDispatcher->dispatch($event);

        if (null !== ($type = $event->getNotificationType())) {
            return $this->notificationCenter->getNotificationsForNotificationType($type);
        }

        return [];
    }

    private function handleLostPasswordModule(): void
    {
        $GLOBALS['TL_DCA']['tl_module']['palettes']['lostPasswordNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['lostPassword'];

        PaletteManipulator::create()
            ->addField('nc_notification', 'reg_password', PaletteManipulator::POSITION_BEFORE)
            ->removeField('reg_password')
            ->applyToPalette('lostPasswordNotificationCenter', 'tl_module')
        ;
    }

    private function handleRegistrationModule(): void
    {
        $GLOBALS['TL_DCA']['tl_module']['palettes']['registrationNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['registration'];

        PaletteManipulator::create()
            ->addField('nc_notification', 'reg_activate')
            ->addField('nc_registration_auto_activate', 'nc_notification')
            ->removeField('reg_activate')
            ->applyToPalette('registrationNotificationCenter', 'tl_module')
        ;
    }

    private function handlePersonalDataModule(): void
    {
        PaletteManipulator::create()
            ->addField('nc_notification', 'config_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('personalData', 'tl_module')
        ;
    }

    private function handleNewsletterSubscribeModule(): void
    {
        $GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterSubscribeNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['subscribe'];

        PaletteManipulator::create()
            ->addField('nc_notification', 'email_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('nc_activation_notification', 'nc_notification')
            ->addField('nc_newsletter_activation_jumpTo', 'redirect_legend', PaletteManipulator::POSITION_APPEND)
            ->removeField('nl_subscribe')
            ->applyToPalette('newsletterSubscribeNotificationCenter', 'tl_module')
        ;
    }

    private function handleNewsletterUnubscribeModule(): void
    {
        $GLOBALS['TL_DCA']['tl_module']['palettes']['newsletterUnsubscribeNotificationCenter'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['unsubscribe'];

        PaletteManipulator::create()
            ->addField('nc_notification', 'email_legend', PaletteManipulator::POSITION_APPEND)
            ->removeField('nl_unsubscribe')
            ->applyToPalette('newsletterUnsubscribeNotificationCenter', 'tl_module')
        ;
    }
}
