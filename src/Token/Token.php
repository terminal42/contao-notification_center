<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Util\Stringable\FileUpload;
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
        if (\is_array($value)) {
            try {
                $value = FileUpload::fromSuperGlobal($value);
            } catch (\Exception) {
                // noop
            }
        }

        $value = match (true) {
            \is_array($value) => new StringableArray($value),
            \is_string($value), $value instanceof \Stringable => $value,
            default => get_debug_type($value),
        };

        return new self($definition, $name, $value);
    }
}
