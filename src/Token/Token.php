<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Util\Stringable\StringableArray;

class Token
{
    public function __construct(private TokenDefinitionInterface $definition, private string $name, private \Stringable|string $value)
    {
    }

    public function getDefinition(): TokenDefinitionInterface
    {
        return $this->definition;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): \Stringable|string
    {
        return $this->value;
    }

    public static function fromMixedValue(TokenDefinitionInterface $definition, string $name, mixed $value): self
    {
        $value = match (true) {
            \is_string($value), $value instanceof \Stringable => $value,
            \is_array($value) => new StringableArray($value),
            default => get_debug_type($value),
        };

        return new self($definition, $name, $value);
    }
}
