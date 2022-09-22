<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class NotificationConfig extends AbstractConfig
{
    public function getId(): int
    {
        return $this->getInt('id');
    }

    public function getType(): string
    {
        return $this->get('type', '');
    }
}
