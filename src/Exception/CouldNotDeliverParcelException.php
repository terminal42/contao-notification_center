<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

class CouldNotDeliverParcelException extends \RuntimeException implements ExceptionInterface
{
    public static function becauseOfNoGatewayInformationProvided(): self
    {
        return new self('The parcel did not contain any gateway information.');
    }

    public static function becauseOfNonExistentGateway(string $gateway): self
    {
        return new self(sprintf('The gateway "%s" does not exist.', $gateway));
    }

    public static function becauseOfNoGatewayIsResponsibleForParcel(string $parcelClass): self
    {
        return new self(sprintf('No gateway is responsible for the parcel class "%s".', $parcelClass));
    }

    public static function becauseOfNotificationIdNotFound(int $id): self
    {
        return new self(sprintf('The notification with ID "%d" does not exist.', $id));
    }
}
