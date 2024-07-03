<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class BackendAttachmentsStamp implements StampInterface
{
    /**
     * @param array<string> $vouchers
     */
    public function __construct(private readonly array $vouchers)
    {
    }

    public function toArray(): array
    {
        return $this->vouchers;
    }

    public static function fromArray(array $data): StampInterface
    {
        return new self($data);
    }
}
