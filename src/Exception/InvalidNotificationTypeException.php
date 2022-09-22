<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class InvalidNotificationTypeException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function becauseTypeDoesNotExist(string $notificationType): self
    {
        return new self(sprintf('The notification type "%s" does not exist.', $notificationType));
    }
}
