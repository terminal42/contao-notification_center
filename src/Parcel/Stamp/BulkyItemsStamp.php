<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;

class BulkyItemsStamp implements StampInterface
{
    /**
     * @var array<string>
     */
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

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        return array_values($this->vouchers);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return array<string>
     */
    public function all(): array
    {
        return $this->toArray();
    }

    private function add(string $voucher): void
    {
        if (!BulkyItemStorage::validateVoucherFormat($voucher)) {
            throw new \InvalidArgumentException('Invalid bulky item voucher format.');
        }

        $this->vouchers[$voucher] = $voucher;
    }
}
