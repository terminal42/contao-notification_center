<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class CouldNotDeliverParcelException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @template T of StampInterface
     *
     * @param array<class-string<T>> $provided
     * @param array<class-string<T>> $required
     */
    public static function becauseOfInsufficientStamps(array $provided, array $required): self
    {
        return new self(sprintf(
            'The parcel did not contain all required stamps. Provided: [%s], Required: [%s].',
            implode(', ', $provided),
            implode(', ', $required)
        ));
    }

    public static function becauseOfNoGatewayInformationProvided(): self
    {
        return new self('The parcel did not contain any gateway information.');
    }

    public static function becauseOfNonExistentGateway(string $gateway): self
    {
        return new self(sprintf('The gateway "%s" does not exist.', $gateway));
    }

    public static function becauseNoGatewayWasDefinedForParcel(): self
    {
        return new self('No gateway was defined for the parcel.');
    }

    public static function becauseOfNotificationIdNotFound(int $id): self
    {
        return new self(sprintf('The notification with ID "%d" does not exist.', $id));
    }
}
