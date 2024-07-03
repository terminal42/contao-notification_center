<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

interface StampInterface
{
    /**
     * Must return a JSON serializable array.
     *
     * @return array<mixed>
     */
    public function toArray(): array;

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self;
}
