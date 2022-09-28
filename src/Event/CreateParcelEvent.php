<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;

class CreateParcelEvent extends Event
{
    private bool $shouldDeliver = true;

    public function __construct(private Parcel $parcel)
    {
    }

    public function getParcel(): Parcel
    {
        return $this->parcel;
    }

    public function setParcel(Parcel $parcel): self
    {
        $this->parcel = $parcel;

        return $this;
    }

    public function disableDelivery(): self
    {
        $this->shouldDeliver = false;

        return $this;
    }

    public function enableDelivery(): self
    {
        $this->shouldDeliver = true;

        return $this;
    }

    public function shouldDeliver(): bool
    {
        return $this->shouldDeliver;
    }
}
