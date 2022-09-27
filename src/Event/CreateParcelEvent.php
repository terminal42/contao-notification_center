<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;

class CreateParcelEvent extends Event
{
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
}
