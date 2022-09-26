<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class GatewayConfig extends AbstractConfig
{
    public function getType(): string
    {
        return $this->getString('type');
    }

    public function getTitle(): string
    {
        return $this->getString('title');
    }
}
