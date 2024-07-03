<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

final class StampCollection
{
    /**
     * @var array<class-string, StampInterface>
     */
    private array $stamps = [];

    /**
     * @param array<StampInterface> $stamps
     */
    public function __construct(array $stamps = [])
    {
        foreach ($stamps as $stamp) {
            $this->add($stamp);
        }
    }

    /**
     * @return array<class-string, StampInterface>
     */
    public function all(): array
    {
        return $this->stamps;
    }

    /**
     * @template T of StampInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function get(string $className): StampInterface|null
    {
        return $this->stamps[$className] ?? null;
    }

    public function with(StampInterface $stamp): self
    {
        $clone = clone $this;

        $clone->add($stamp);

        return $clone;
    }

    public function has(string $class): bool
    {
        return \array_key_exists($class, $this->stamps);
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    public function getClasses(): array
    {
        return array_keys($this->stamps);
    }

    /**
     * @return array<class-string<StampInterface>, array<mixed>>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->stamps as $stamp) {
            $data[$stamp::class] = $stamp->toArray();
        }

        return $data;
    }

    /**
     * @param array<class-string<StampInterface>, array<mixed>> $data
     */
    public static function fromArray(array $data): self
    {
        $stamps = [];

        foreach ($data as $class => $stampValue) {
            if (!class_exists($class) || !is_a($class, StampInterface::class, true)) {
                continue;
            }

            $stamps[] = $class::fromArray($stampValue);
        }

        return new self($stamps);
    }

    private function add(StampInterface $stamp): self
    {
        $this->stamps[$stamp::class] = $stamp;

        return $this;
    }
}
