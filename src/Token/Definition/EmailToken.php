<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Token\TokenInterface;

class EmailToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'email';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }

    public function createToken(string $tokenName, mixed $value): TokenInterface
    {
        return $this->createTokenWithAllowedTypes(
            $tokenName,
            $value,
            self::DEFINITION_NAME,
            ['null', 'string']
        );
    }
}
