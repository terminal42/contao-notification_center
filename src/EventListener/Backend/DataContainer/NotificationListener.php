<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;

class NotificationListener
{
    public function __construct(private readonly NotificationTypeRegistry $typeRegistry)
    {
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_nc_notification', target: 'fields.type.options')]
    public function onTypeOptionsCallback(): array
    {
        return array_keys($this->typeRegistry->all());
    }
}
