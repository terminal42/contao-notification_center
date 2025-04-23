<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

class AsynchronousDeliveryStamp implements StampInterface
{
    public function __construct(public string $identifier)
    {
        $length = \strlen($this->identifier);
        if ($length < 1 || $length > 64) {
            throw new \InvalidArgumentException('The identifier length must be between 1 and 64 characters.');
        }
    }

    public function toArray(): array
    {
        return ['identifier' => $this->identifier];
    }

    public static function fromArray(array $data): StampInterface
    {
        return new self($data['identifier']);
    }

    public static function createWithRandomId(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }
}
