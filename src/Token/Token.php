<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class Token implements TokenInterface
{
    public function __construct(private TokenDefinitionInterface $definition, private string $name, private mixed $value)
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

    public function getValue(): mixed
    {
        return $this->value;
    }
}
