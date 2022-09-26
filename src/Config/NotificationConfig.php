<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class NotificationConfig extends AbstractConfig
{
    public function getType(): string
    {
        return $this->getString('type');
    }

    public function getTitle(): string
    {
        return $this->getString('title');
    }

    public function getTokenTransformerTemplate(): string
    {
        return $this->getString('token_transformer');
    }
}
