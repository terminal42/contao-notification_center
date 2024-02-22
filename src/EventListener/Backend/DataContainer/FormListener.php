<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType;

class FormListener
{
    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_form', target: 'fields.nc_notification.options')]
    public function onNotificationOptionsCallback(DataContainer $dc): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(FormGeneratorNotificationType::NAME);
    }
}
