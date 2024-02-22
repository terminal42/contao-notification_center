<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Receipt;

use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;

final class Receipt
{
    private CouldNotDeliverParcelException|null $exception = null;

    private function __construct(
        private readonly Parcel $parcel,
        private readonly bool $wasDelivered,
    ) {
    }

    public function getParcel(): Parcel
    {
        return $this->parcel;
    }

    public function wasDelivered(): bool
    {
        return $this->wasDelivered;
    }

    public function getException(): CouldNotDeliverParcelException|null
    {
        return $this->exception;
    }

    public static function createForSuccessfulDelivery(Parcel $parcel): self
    {
        return new self($parcel, true);
    }

    public static function createForUnsuccessfulDelivery(Parcel $parcel, CouldNotDeliverParcelException $exception): self
    {
        $receipt = new self($parcel, false);
        $receipt->exception = $exception;

        return $receipt;
    }
}
