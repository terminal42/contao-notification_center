<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class InvalidRawTokenFormatException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function becauseOfUnknownType(string $type, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('Cannot create a token from raw value. "%s" is not supported.', $type), $code, $previous);
    }
}
