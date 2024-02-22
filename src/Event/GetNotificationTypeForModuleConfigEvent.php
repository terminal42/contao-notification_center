<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Config\ModuleConfig;

class GetNotificationTypeForModuleConfigEvent extends Event
{
    private string|null $notificationType = null;

    public function __construct(
        private readonly ModuleConfig $moduleConfig,
        private readonly string $field,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getModuleConfig(): ModuleConfig
    {
        return $this->moduleConfig;
    }

    public function getNotificationType(): string|null
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): self
    {
        $this->notificationType = $notificationType;

        $this->stopPropagation();

        return $this;
    }
}
