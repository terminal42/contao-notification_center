<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

class ArrayToken extends AbstractToken
{
    public function __construct(array $tokenData, string $tokenName, string $tokenDefinitionName)
    {
        parent::__construct($tokenData, $tokenName, $tokenDefinitionName);
    }

    public function getParserValue(): string
    {
        $chunks = [];

        foreach ($this->tokenData as $k => $v) {
            if (!\is_string($v)) {
                $chunks[] = $k.' ['.json_encode($v).']';
            } else {
                $chunks[] = $v;
            }
        }

        return implode(', ', $chunks);
    }
}
