<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;
use Terminal42\NotificationCenterBundle\Token\TokenInterface;

class WildcardToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'wildcard';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }

    public function matchesTokenName(string $tokenName): bool
    {
        return (bool) preg_match('/^'.preg_quote(substr($this->getTokenName(), 0, -1), '/').'.+$/', $tokenName);
    }

    public function createToken(string $tokenName, mixed $value): TokenInterface
    {
        return $this->createTokenWithAllowedTypes(
            $tokenName,
            $value,
            ['null', 'string', 'array']
        );
    }

    /**
     * @throws InvalidTokenNameException
     */
    protected function validateTokenName(string $name): void
    {
        if (!str_ends_with($name, '_*')) {
            throw InvalidTokenNameException::becauseMustEndWith('_*');
        }
    }
}
