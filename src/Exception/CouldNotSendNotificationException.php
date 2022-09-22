<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class CouldNotSendNotificationException extends \RuntimeException implements ExceptionInterface
{
    public static function becauseOfIdNotFound(int $id): self
    {
        return new self(sprintf('The notification with ID "%d" does not exist.', $id));
    }
}
