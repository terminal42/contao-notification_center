<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;

class WildcardToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'wildcard';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
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

    public function matchesTokenName(string $tokenName): bool
    {
        return (bool) preg_match('/^'.preg_quote(substr($this->getTokenName(), 0, -1), '/').'.+$/', $tokenName);
    }
}
