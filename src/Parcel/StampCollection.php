<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

class StampCollection
{
    /**
     * @var array<class-string,StampInterface>
     */
    private array $stamps = [];

    private bool $sealed = false;

    /**
     * @param array<StampInterface> $stamps
     */
    public function __construct(array $stamps = [])
    {
        foreach ($stamps as $stamp) {
            $this->add($stamp);
        }
    }

    public function seal(): self
    {
        if ($this->isSealed()) {
            return $this;
        }

        $clone = clone $this;
        $clone->sealed = true;

        return $clone;
    }

    public function isSealed(): bool
    {
        return $this->sealed;
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
        if ($this->isSealed()) {
            throw new \LogicException('Cannot add a stamp to a sealed collection.');
        }

        $clone = clone $this;

        $clone->add($stamp);

        return $clone;
    }

    public function has(string $class): bool
    {
        return \array_key_exists($class, $this->stamps);
    }

    /**
     * @param array<class-string<StampInterface>> $classes
     */
    public function hasMultiple(array $classes): bool
    {
        foreach ($classes as $class) {
            if (!$this->has($class)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    public function getClasses(): array
    {
        return array_keys($this->stamps);
    }

    public function serialize(): string
    {
        $data = [
            'stamps' => [],
            'sealed' => $this->sealed,
        ];

        foreach ($this->stamps as $stamp) {
            $data['stamps'][$stamp::class] = $stamp->serialize();
        }

        return json_encode($data);
    }

    public static function fromSerialized(string $serialized): self
    {
        $data = json_decode($serialized, true);
        $stamps = [];

        foreach ($data['stamps'] as $class => $stampValue) {
            if (!class_exists($class) || !is_a($class, StampInterface::class, true)) {
                continue;
            }

            $stamps[] = $class::fromSerialized($stampValue);
        }

        $collection = new self($stamps);
        $collection->sealed = $data['sealed'];

        return $collection;
    }

    private function add(StampInterface $stamp): self
    {
        if ($this->isSealed()) {
            throw new \LogicException('Cannot add a stamp to a sealed parcel.');
        }

        $this->stamps[$stamp::class] = $stamp;

        return $this;
    }
}
