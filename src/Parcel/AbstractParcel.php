<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

abstract class AbstractParcel implements ParcelInterface
{
    /**
     * @var array<class-string,StampInterface>
     */
    private array $stamps = [];

    public function hasStamp(string $class): bool
    {
        return \array_key_exists($class, $this->stamps);
    }

    /**
     * @template T of StampInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getStamp(string $className): StampInterface|null
    {
        return $this->stamps[$className] ?? null;
    }

    private function addStamp(StampInterface $stamp): self
    {
        $this->stamps[$stamp::class] = $stamp;

        return $this;
    }

    public function withStamp(StampInterface $stamp): static
    {
        $clone = clone $this;

        $clone->addStamp($stamp);

        return $clone;
    }

    public function serialize(): string
    {
        return json_encode($this->forSerialization());
    }

    /**
     * @return array<string, string>
     */
    protected function forSerialization(): array
    {
        $data = [];

        foreach ($this->stamps as $stamp) {
            $data[$stamp::class] = $stamp->serialize();
        }

        return $data;
    }
}
