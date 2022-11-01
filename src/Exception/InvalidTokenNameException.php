<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class InvalidTokenNameException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function becauseMustNotEndWith(string $suffix, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('The token name is invalid because ist must not end with "%s".', $suffix), $code, $previous);
    }

    public static function becauseMustEndWith(string $suffix, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('The token name is invalid because ist must end with "%s".', $suffix), $code, $previous);
    }
}
