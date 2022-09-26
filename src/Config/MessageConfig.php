<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class MessageConfig extends AbstractConfig
{
    public function getTitle(): string
    {
        return $this->getString('title');
    }

    public function getNotification(): int
    {
        return $this->getInt('pid');
    }

    public function getGateway(): int
    {
        return $this->getInt('gateway');
    }

    public function isPublished(): bool
    {
        return $this->getBoolean('published');
    }
}
