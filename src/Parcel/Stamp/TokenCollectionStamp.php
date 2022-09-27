<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class TokenCollectionStamp implements StampInterface
{
    public function __construct(public TokenCollection $tokenCollection)
    {
    }

    public function serialize(): string
    {
        return $this->tokenCollection->serialize();
    }
}
