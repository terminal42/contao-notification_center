<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class InvalidTokenException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param array<string> $allowedTypes
     */
    public static function becauseOfIncorrectType(array $allowedTypes, string $wrongType, int $code = 0, \Throwable|null $previous = null): self
    {
        return new self(sprintf('Cannot create a token from raw value. Must be one of "%s". "%s" given.', implode('|', $allowedTypes), $wrongType), $code, $previous);
    }
}
