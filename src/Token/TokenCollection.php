<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

class TokenCollection
{
    private $tokens = [];

    public function __construct(array $tokens = [])
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    public function all(): array
    {
        return $this->tokens;
    }

    public function add(TokenInterface $token): self
    {
        $this->tokens[] = $token;

        return $this;
    }

    public function asRawKeyValue(): array
    {
        $values = [];

        foreach ($this->all() as $token) {
            $values[$token->getName()] = $token->getValue();
        }

        return $values;
    }

    public function asRawKeyValueWithStringsOnly(): array
    {
        $values = [];

        foreach ($this->asRawKeyValue() as $k => $value) {
            if (\is_string($value)) {
                $values[$k] = $value;
            }
        }

        return $values;
    }
}
