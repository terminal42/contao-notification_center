<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;

abstract class AbstractTokenDefinition implements TokenDefinitionInterface
{
    final public function __construct(private string $name, private string $translationKey)
    {
        $this->validateToken($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @throws InvalidTokenNameException
     */
    protected function validateToken(string $name): void
    {
    }

    public static function createWithTranslationKeyPrefix(string $name, string $prefix): static
    {
        return new static($name, $prefix.$name);
    }

    public function matchesTokenName(string $tokenName): bool
    {
        return $tokenName === $this->name;
    }
}
