<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

class LocaleStamp implements StampInterface
{
    public function __construct(public string $locale)
    {
    }

    public function serialize(): string
    {
        return $this->locale;
    }

    public static function fromSerialized(string $serialized): StampInterface
    {
        return new self($serialized);
    }
}
