<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

interface ParcelInterface
{
    public function hasStamp(string $class): bool;

    /**
     * @template T of StampInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getStamp(string $className): StampInterface|null;

    public function withStamp(StampInterface $stamp): static;

    public function serialize(): string;
}
