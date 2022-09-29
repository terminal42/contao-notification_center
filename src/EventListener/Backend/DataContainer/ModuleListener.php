<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\GetMessageTypeForModuleConfigEvent;
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

        if ('lostPassword' !== $moduleConfig->getType()) {
            return;
        }

        // Add the notification choice before the core lost password text field (reg_password)
        $pm = PaletteManipulator::create()
            ->addField('nc_notification', 'reg_password', PaletteManipulator::POSITION_BEFORE)
        ;

        // If a notification was selected, hide the "reg_password" field
        if ($moduleConfig->getInt('nc_notification') > 0) {
            $pm->removeField('reg_password');
        } else {
            $GLOBALS['TL_DCA']['tl_module']['fields']['reg_password']['eval']['tl_class'] = 'clr';
        }

        $pm->applyToPalette('lostPassword', 'tl_module');
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.nc_notification.options')]
    public function onNotificationOptionsCallback(DataContainer $dc): array
    {
        if (null === ($moduleConfig = $this->configLoader->loadModule((int) $dc->id))) {
            return [];
        }

        $event = new GetMessageTypeForModuleConfigEvent($moduleConfig);

        $this->eventDispatcher->dispatch($event);

        if (null !== ($type = $event->getMessageType())) {
            return $this->notificationCenter->getNotificationsForMessageType($type);
        }

        return [];
    }
}
