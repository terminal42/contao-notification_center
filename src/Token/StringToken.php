<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

final class StringToken extends AbstractToken
{
    public function __construct(string $tokenValue, string $tokenName)
    {
        parent::__construct($tokenValue, $tokenName);
    }

    public function getValue(): string
    {
        return $this->tokenValue;
    }

    public function getParserValue(): string
    {
        return $this->tokenValue;
    }

    public static function fromArray(array $data): static
    {
        return new self((string) $data['value'], $data['name']);
    }
}
