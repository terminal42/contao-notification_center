<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

interface StampInterface
{
    public function serialize(): string;

    public static function fromSerialized(string $serialized): self;
}
