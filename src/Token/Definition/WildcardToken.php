<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenNameException;

class WildcardToken extends AbstractTokenDefinition
{
    /**
     * @throws InvalidTokenNameException
     */
    protected function validateToken(string $name): void
    {
        if (!str_ends_with($name, '_*')) {
            throw InvalidTokenNameException::becauseMustEndWith('_*');
        }
    }

    public function matchesTokenName(string $tokenName): bool
    {
        return (bool) preg_match('/^'.preg_quote(substr($this->getName(), 0, -1), '/').'.+$/', $tokenName);
    }
}
