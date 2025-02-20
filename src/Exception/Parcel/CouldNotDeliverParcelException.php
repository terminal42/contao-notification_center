<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception\Parcel;

use Terminal42\NotificationCenterBundle\Exception\ExceptionInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class CouldNotDeliverParcelException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @template T of StampInterface
     *
     * @param array<class-string<T>> $provided
     * @param array<class-string<T>> $required
     */
    public static function becauseOfInsufficientStamps(array $provided, array $required, int $code = 0, \Throwable|null $previous = null): self
    {
        return new self(\sprintf(
            'The parcel did not contain all required stamps. Provided: [%s], Required: [%s].',
            implode(', ', $provided),
            implode(', ', $required),
        ), $code, $previous);
    }

    public static function becauseNoGatewayWasDefinedForParcel(int $code = 0, \Throwable|null $previous = null): self
    {
        return new self('No gateway was defined for the parcel.', $code, $previous);
    }

    public static function becauseParcelCouldNotBeSealed(CouldNotSealParcelException $exception): self
    {
        return new self('Parcel could not be sealed: '.$exception->getMessage(), $exception->getCode(), $exception);
    }

    public static function becauseOfGatewayException(string $gatewayType, int $code = 0, \Throwable|null $exception = null): self
    {
        return new self(\sprintf(
            'The parcel could not be delivered via the "%s" gateway because of an internal issue: %s.',
            $gatewayType,
            $exception->getMessage(),
        ), $code, $exception);
    }
}
