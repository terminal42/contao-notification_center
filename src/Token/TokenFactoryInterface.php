<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Exception\InvalidRawTokenFormatException;

interface TokenFactoryInterface
{
    /**
     * @throws InvalidRawTokenFormatException in case the raw token value is not supported
     */
    public function createFromRaw(mixed $rawTokenValue, string $tokenName, string $tokenDefinitionName): TokenInterface;
}
