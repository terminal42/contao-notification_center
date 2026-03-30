<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\GetNotificationTypeForModuleConfigEvent;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class ModuleListener
{
    public function __construct(
        private readonly ConfigLoader $configLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NotificationCenter $notificationCenter,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.nc_notification.attributes')]
    public function onAttributesCallback(array $attributes, DataContainer $dc): array
    {
        if ('newsletterSubscribeNotificationCenter' === $dc->getCurrentRecord()['type']) {
            $attributes['mandatory'] = true;
        }

        return $attributes;
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
}
