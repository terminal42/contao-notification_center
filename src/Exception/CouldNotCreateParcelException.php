<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class CouldNotCreateParcelException extends \InvalidArgumentException
{
    public static function becauseOfNonExistentMessage(int $messageId): self
    {
        return new self(sprintf('The message ID "%s" does not exist.', $messageId));
    }

    public static function becauseOfNonExistentNotification(int $notificationId): self
    {
        return new self(sprintf('The notification ID "%s" does not exist.', $notificationId));
    }

    public static function becauseOfNonExistentGateway(int $gatwayId): self
    {
        return new self(sprintf('The gateway ID "%s" does not exist.', $gatwayId));
    }

    public static function becauseOfNonExistentGatewayType(string $gatewayType): self
    {
        return new self(sprintf('The gateway type "%s" does not exist.', $gatewayType));
    }
}
