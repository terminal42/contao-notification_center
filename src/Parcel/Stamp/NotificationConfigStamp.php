<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Config\NotificationConfig;

class NotificationConfigStamp extends AbstractConfigStamp
{
    public function __construct(public NotificationConfig $notificationConfig)
    {
        parent::__construct($this->notificationConfig);
    }
}
