<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

class MessageTypeRegistry
{
    /**
     * @var array<MessageTypeInterface>
     */
    private array $types = [];

    public function __construct(iterable $gateways)
    {
        foreach ($gateways as $gateway) {
            $this->add($gateway);
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

    /**
     * @param class-string<MessageTypeInterface> $type
     */
    public function getByType(string $type): MessageTypeInterface|null
    {
        return $this->types[$name] ?? null;
    }
}
