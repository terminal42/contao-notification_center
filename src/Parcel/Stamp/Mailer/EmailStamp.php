<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer;

use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\UnserializableStampInterface;

class EmailStamp implements UnserializableStampInterface
{
    public function __construct(public Email $email)
    {
    }

    public function serialize(): string
    {
        return base64_encode(serialize($this->email));
    }

    public static function fromSerialized(string $serialized): UnserializableStampInterface
    {
        return new self(unserialize(base64_decode($serialized, true)));
    }
}
