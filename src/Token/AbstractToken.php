<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

abstract class AbstractToken implements TokenInterface
{
    public function __construct(protected mixed $tokenValue, private string $tokenName)
    {
    }

    public function getName(): string
    {
        return $this->tokenName;
    }

    abstract public function getParserValue(): string;

    public function toArray(): array
    {
        return [
            'value' => $this->tokenValue,
            'name' => $this->getName(),
        ];
    }
}
