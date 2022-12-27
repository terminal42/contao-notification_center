<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;

class BulkyItemsStamp implements StampInterface
{
    private array $vouchers = [];

    /**
     * @param array<string> $vouchers
     */
    public function __construct(array $vouchers)
    {
        foreach ($vouchers as $voucher) {
            $this->add($voucher);
        }
    }

    public function has(string $voucher): bool
    {
        return isset($this->vouchers[$voucher]);
    }

    public function get(string $voucher): string|null
    {
        return $this->vouchers[$voucher] ?? null;
    }

    public function serialize(): string
    {
        return json_encode($this->vouchers);
    }

    public static function fromSerialized(string $serialized): StampInterface
    {
        return new self(json_decode($serialized, true));
    }

    private function add(string $voucher): void
    {
        if (!BulkyItemStorage::validateVoucherFormat($voucher)) {
            throw new \InvalidArgumentException('Invalid bulky item voucher format.');
        }

        $this->vouchers[$voucher] = $voucher;
    }
}
