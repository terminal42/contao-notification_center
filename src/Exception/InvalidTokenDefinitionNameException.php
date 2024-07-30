<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class InvalidTokenDefinitionNameException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function becauseDoesNotExist(string $name, int $code = 0, \Throwable|null $previous = null): self
    {
        return new self(\sprintf('The token definition class "%s" does not exist.', $name), $code, $previous);
    }
}
