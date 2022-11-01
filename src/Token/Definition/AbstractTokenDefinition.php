<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;

abstract class AbstractTokenDefinition implements TokenDefinitionInterface
{
    final public function __construct(private string $tokenName, private string $translationKey)
    {
        $this->validateTokenName($this->tokenName);
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @throws InvalidTokenNameException
     */
    protected function validateTokenName(string $name): void
    {
        if (str_ends_with($name, '_*')) {
            throw InvalidTokenNameException::becauseMustNotEndWith('_*');
        }
    }

    public static function create(string $tokenName, string $translationKey): static
    {
        return new static($tokenName, $translationKey);
    }

    public function matchesTokenName(string $tokenName): bool
    {
        return $tokenName === $this->tokenName;
    }
}
