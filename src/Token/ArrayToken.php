<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

class ArrayToken extends AbstractToken
{
    public function __construct(array $tokenValue, string $tokenName)
    {
        parent::__construct($tokenValue, $tokenName);
    }

    public function getParserValue(): string
    {
        $chunks = [];

        foreach ($this->tokenValue as $k => $v) {
            if (!\is_string($v)) {
                $chunks[] = $k.' ['.json_encode($v).']';
            } else {
                $chunks[] = $v;
            }
        }

        return implode(', ', $chunks);
    }

    public static function fromArray(array $data): static
    {
        return new static((array) $data['value'], $data['name']);
    }
}
