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

    public function getStart(): \DateTimeImmutable|null
    {
        if ('' === ($start = $this->getString('start'))) {
            return null;
        }

        return new \DateTimeImmutable('@'.$start);
    }

    public function getStop(): \DateTimeImmutable|null
    {
        if ('' === ($stop = $this->getString('stop'))) {
            return null;
        }

        return new \DateTimeImmutable('@'.$stop);
    }
}
