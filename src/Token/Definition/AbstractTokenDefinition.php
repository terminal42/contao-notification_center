<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenException;
use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;
use Terminal42\NotificationCenterBundle\Token\ArrayToken;
use Terminal42\NotificationCenterBundle\Token\StringToken;
use Terminal42\NotificationCenterBundle\Token\TokenInterface;

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

    public static function createFromNameAndTranslationKey(string $tokenName, string $translationKey): static
    {
        return new static($tokenName, $translationKey);
    }

    public function matchesTokenName(string $tokenName): bool
    {
        return $tokenName === $this->tokenName;
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

    protected function createTokenWithAllowedTypes(string $tokenName, mixed $tokenValue, array $allowedTypes): TokenInterface
    {
        if (\in_array('null', $allowedTypes, true) && null === $tokenValue) {
            return new StringToken('', $tokenName);
        }

        if (\in_array('string', $allowedTypes, true) && \is_scalar($tokenValue)) {
            return new StringToken((string) $tokenValue, $tokenName);
        }

        if (\in_array('array', $allowedTypes, true) && \is_array($tokenValue)) {
            return new ArrayToken($tokenValue, $tokenName);
        }

        throw InvalidTokenException::becauseOfUnknownType(get_debug_type($tokenValue));
    }
}
