<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

class NotificationTypeRegistry
{
    /**
     * @var array<NotificationTypeInterface>
     */
    private array $types = [];

    /**
     * @param iterable<NotificationTypeInterface> $notificationTypes
     */
    public function __construct(iterable $notificationTypes)
    {
        foreach ($notificationTypes as $notificationType) {
            $this->add($notificationType);
        }
    }

    public function add(NotificationTypeInterface $type): self
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }

    /**
     * @return array<string, NotificationTypeInterface>
     */
    public function all(): array
    {
        return $this->types;
    }

    public function getByName(string $name): NotificationTypeInterface|null
    {
        return $this->types[$name] ?? null;
    }
}
