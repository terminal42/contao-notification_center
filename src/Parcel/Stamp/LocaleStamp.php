<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

class LocaleStamp implements StampInterface
{
    public function __construct(public string $locale)
    {
    }

    public function toArray(): array
    {
        return ['locale' => $this->locale];
    }

    public static function fromArray(array $data): StampInterface
    {
        return new self($data['locale']);
    }
}
