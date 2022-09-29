<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Config\ModuleConfig;

class GetMessageTypeForModuleConfigEvent extends Event
{
    private string|null $messageType = null;

    public function __construct(private ModuleConfig $moduleConfig)
    {
    }

    public function getModuleConfig(): ModuleConfig
    {
        return $this->moduleConfig;
    }

    public function getMessageType(): string|null
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        $this->stopPropagation();

        return $this;
    }
}
