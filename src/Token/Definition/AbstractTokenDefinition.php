<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Parcel\StampCollection;
use Terminal42\NotificationCenterBundle\Token\Token;

abstract class AbstractTokenDefinition implements TokenDefinitionInterface
{
    final public function __construct(
        private readonly string $tokenName,
        private readonly string $translationKey,
    ) {
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function matches(string $tokenName, mixed $value): bool
    {
        if (str_ends_with($this->getTokenName(), '_*')) {
            return (bool) preg_match('/^'.preg_quote(substr($this->getTokenName(), 0, -1), '/').'.+$/', $tokenName);
        }

        return $this->getTokenName() === $tokenName;
    }

    public function createToken(string $tokenName, mixed $value, StampCollection|null $stamps = null): Token
    {
        return Token::fromValue($tokenName, $value);
    }
}
