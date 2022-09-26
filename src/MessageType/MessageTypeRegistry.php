<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

class MessageTypeRegistry
{
    /**
     * @var array<MessageTypeInterface>
     */
    private array $types = [];

    /**
     * @param iterable<MessageTypeInterface> $messageTypes
     */
    public function __construct(iterable $messageTypes)
    {
        foreach ($messageTypes as $messageType) {
            $this->add($messageType);
        }
    }

    public function add(MessageTypeInterface $type): self
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }

    /**
     * @return array<string,MessageTypeInterface>
     */
    public function all(): array
    {
        return $this->types;
    }

    public function getByName(string $name): MessageTypeInterface|null
    {
        return $this->types[$name] ?? null;
    }
}
