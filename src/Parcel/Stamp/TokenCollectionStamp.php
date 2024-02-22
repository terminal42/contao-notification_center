<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class TokenCollectionStamp implements StampInterface
{
    public function __construct(public TokenCollection $tokenCollection)
    {
    }

    public function toArray(): array
    {
        return $this->tokenCollection->toSerializableArray();
    }

    public static function fromArray(array $data): self
    {
        return new self(TokenCollection::fromSerializedArray($data));
    }
}
