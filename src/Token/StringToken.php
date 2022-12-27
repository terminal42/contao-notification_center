<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

class StringToken extends AbstractToken
{
    public function __construct(string $tokenData, string $tokenName, string $tokenDefinitionName)
    {
        parent::__construct($tokenData, $tokenName, $tokenDefinitionName);
    }

    public function getParserValue(): string
    {
        return $this->tokenData;
    }
}
