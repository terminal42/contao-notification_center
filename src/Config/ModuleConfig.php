<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class ModuleConfig extends AbstractConfig
{
    public function getType(): string
    {
        return $this->getString('type');
    }
}
