<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception\Parcel;

use Terminal42\NotificationCenterBundle\Exception\ExceptionInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class CouldNotSealParcelException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @template T of StampInterface
     *
     * @param array<class-string<T>> $provided
     * @param array<class-string<T>> $required
     */
    public static function becauseOfInsufficientStamps(array $provided, array $required, int $code = 0, \Throwable|null $previous = null): self
    {
        return new self(sprintf(
            'The parcel did not contain all required stamps. Provided: [%s], Required: [%s].',
            implode(', ', $provided),
            implode(', ', $required),
        ), $code, $previous);
    }
}
