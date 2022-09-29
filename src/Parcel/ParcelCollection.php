<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Parcel>
 */
class ParcelCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Parcel::class;
    }
}
