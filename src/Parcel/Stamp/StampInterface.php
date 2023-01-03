<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

interface StampInterface
{
    /**
     * Must return a JSON serializable array.
     */
    public function toArray(): array;

    public static function fromArray(array $data): self;
}
