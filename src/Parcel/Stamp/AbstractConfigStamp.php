<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Config\AbstractConfig;

abstract class AbstractConfigStamp implements StampInterface
{
    public function __construct(private AbstractConfig $config)
    {
    }

    public function serialize(): string
    {
        return $this->config->serialize();
    }
}
