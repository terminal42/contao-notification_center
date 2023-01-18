<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Token\TokenInterface;

class HtmlToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'html';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }

    public function createToken(mixed $value, string $tokenName = null): TokenInterface
    {
        return $this->createTokenWithAllowedTypes(
            $value,
            ['null', 'string'],
            $tokenName,
        );
    }
}
