<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Exception\InvalidRawTokenFormatException;

class TokenFactory implements TokenFactoryInterface
{
    /**
     * @throws InvalidRawTokenFormatException in case the raw token value is not supported
     */
    public function createFromRaw(mixed $rawTokenValue, string $tokenName, string $tokenDefinitionName): TokenInterface
    {
        if (\is_scalar($rawTokenValue) || null === $rawTokenValue) {
            return new StringToken((string) $rawTokenValue, $tokenName, $tokenDefinitionName);
        }

        if (\is_array($rawTokenValue)) {
            return new ArrayToken($rawTokenValue, $tokenName, $tokenDefinitionName);
        }

        throw InvalidRawTokenFormatException::becauseOfUnknownType(get_debug_type($rawTokenValue));
    }
}
