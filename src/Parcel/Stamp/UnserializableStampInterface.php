<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

interface UnserializableStampInterface extends StampInterface
{
    public static function fromSerialized(string $serialized): self;
}
