<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class GatewayConfig extends AbstractConfig
{
    public function getType(): string
    {
        return $this->get('type', '');
    }

    public function getTitle(): string
    {
        return $this->get('title', '');
    }
}
